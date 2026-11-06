<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\Table;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // Helper to get active table from query ?meja=XX, ?table=XX, or session
    private function resolveTable(Request $request): array
    {
        $tableNum = null;
        if ($request->has('meja')) {
            $val = $request->get('meja');
            $tableNum = str_starts_with($val, 'Meja') ? $val : 'Meja ' . str_pad($val, 2, '0', STR_PAD_LEFT);
        } elseif ($request->has('table')) {
            $val = $request->get('table');
            $tableNum = str_starts_with($val, 'Meja') ? $val : 'Meja ' . str_pad($val, 2, '0', STR_PAD_LEFT);
        }

        if ($tableNum) {
            session(['current_table' => $tableNum]);
        }

        $tableName = session('current_table', 'Meja 08');
        $tableRecord = Table::where('table_number', $tableName)->first();

        return [$tableName, $tableRecord];
    }

    // Layar 1: Menu Utama
    public function index(Request $request)
    {
        [$table, $tableRecord] = $this->resolveTable($request);
        $setting = RestaurantSetting::getSettings();

        $selectedCategory = $request->get('category', 'Semua');
        $search = trim($request->get('q', ''));

        $query = Product::where('is_available', true)->with(['category', 'options.choices']);

        if ($selectedCategory && $selectedCategory !== 'Semua') {
            $query->whereHas('category', function ($q) use ($selectedCategory) {
                $q->where('name', $selectedCategory);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('composition', 'like', "%{$search}%");
            });
        }

        $menuItems = $query->orderBy('is_signature', 'desc')->get();
        $categories = array_merge(['Semua'], Category::orderBy('sort_order')->pluck('name')->toArray());

        $cart = session('cart', []);
        $cartCount = array_sum(array_column($cart, 'quantity'));
        $subtotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));

        return view('menu.index', compact('menuItems', 'categories', 'selectedCategory', 'search', 'table', 'setting', 'cart', 'cartCount', 'subtotal'));
    }

    // Layar 2: Detail Makanan
    public function show($slug, Request $request)
    {
        [$table, $tableRecord] = $this->resolveTable($request);
        $dish = Product::where('slug', $slug)->with(['category', 'options.choices'])->firstOrFail();
        $setting = RestaurantSetting::getSettings();

        return view('menu.show', compact('dish', 'table', 'setting'));
    }

    // Layar 4: Keranjang Belanja
    public function cart(Request $request)
    {
        [$table, $tableRecord] = $this->resolveTable($request);
        $setting = RestaurantSetting::getSettings();
        $cart = session('cart', []);

        $subtotal = 0;
        $totalItems = 0;
        foreach ($cart as $c) {
            $subtotal += $c['price'] * $c['quantity'];
            $totalItems += $c['quantity'];
        }

        $serviceFee = (int) round($subtotal * ($setting->service_fee_pct / 100));
        $pbjt = (int) round($subtotal * ($setting->pbjt_tax_pct / 100));
        $grandTotal = $subtotal + $serviceFee + $pbjt;

        return view('cart.index', compact('table', 'cart', 'subtotal', 'totalItems', 'serviceFee', 'pbjt', 'grandTotal', 'setting'));
    }

    // Tambah Item ke Keranjang (Harga dihitung dari DB untuk keamanan!)
    public function addToCart(Request $request)
    {
        $request->validate([
            'dish_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::with('options.choices')->findOrFail($request->input('dish_id'));
        $quantity = (int) $request->input('quantity', 1);
        $note = trim($request->input('note', ''));
        $userOptions = $request->input('options', []);

        // Stock Verification
        if (!$product->isAvailableToOrder() || $product->isOutOfStock()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => "Maaf, stok {$product->name} telah habis untuk hari ini."], 422);
            }
            return back()->with('error', "Maaf, stok {$product->name} telah habis untuk hari ini.");
        }

        // Check against current cart quantities
        $cart = session('cart', []);
        $existingQtyInCart = 0;
        foreach ($cart as $cItem) {
            if (($cItem['product_id'] ?? null) == $product->id) {
                $existingQtyInCart += $cItem['quantity'];
            }
        }

        if ($product->track_stock && ($existingQtyInCart + $quantity > $product->stock)) {
            $available = max(0, $product->stock - $existingQtyInCart);
            $msg = $available > 0
                ? "Maaf, Anda sudah memiliki {$existingQtyInCart} di keranjang. Sisa stok yang dapat ditambahkan hanya {$available} porsi."
                : "Maaf, stok {$product->name} ({$product->stock} porsi) sudah ada di keranjang Anda.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $extraPrice = 0;
        $optionsSummary = [];

        if (is_array($userOptions) && !empty($userOptions)) {
            foreach ($product->options as $opt) {
                if (isset($userOptions[$opt->id])) {
                    $selectedChoiceName = $userOptions[$opt->id];
                    $choice = $opt->choices->firstWhere('name', $selectedChoiceName);
                    if ($choice) {
                        $extraPrice += $choice->extra_price;
                        $optionsSummary[] = $choice->name;
                    }
                }
            }
        }

        // Server-verified unit price
        $unitPrice = $product->price + $extraPrice;
        $cartItemId = $product->id . '-' . time() . '-' . rand(100, 999);

        $cart = session('cart', []);
        $cart[$cartItemId] = [
            'id' => $cartItemId,
            'product_id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'image' => $product->image,
            'price' => $unitPrice,
            'quantity' => $quantity,
            'options' => $optionsSummary,
            'options_text' => implode(' · ', $optionsSummary),
            'note' => $note,
            'is_ai_pick' => $request->boolean('is_ai_pick', false),
        ];

        session(['cart' => $cart]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'cart_count' => array_sum(array_column($cart, 'quantity'))]);
        }

        return redirect()->route('cart')->with('success', "{$product->name} berhasil ditambahkan ke keranjang.");
    }

    // Update Jumlah Pesanan di Keranjang
    public function updateCart(Request $request)
    {
        $id = $request->input('id');
        $delta = (int) $request->input('delta', 1);

        $cart = session('cart', []);

        if (isset($cart[$id])) {
            if ($delta > 0) {
                $product = Product::find($cart[$id]['product_id'] ?? null);
                if ($product && $product->track_stock) {
                    $totalInCart = 0;
                    foreach ($cart as $k => $cItem) {
                        if (($cItem['product_id'] ?? null) == $product->id) {
                            $totalInCart += $cItem['quantity'];
                        }
                    }

                    if ($totalInCart + $delta > $product->stock) {
                        $msg = "Stok maksimal {$product->name} tercapai (tersisa {$product->stock} porsi).";
                        if ($request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $msg], 422);
                        }
                        return redirect()->route('cart')->with('error', $msg);
                    }
                }
            }

            $cart[$id]['quantity'] += $delta;
            if ($cart[$id]['quantity'] <= 0) {
                unset($cart[$id]);
            }
        }

        session(['cart' => $cart]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'cart' => $cart]);
        }

        return redirect()->route('cart');
    }

    // Layar 5: Checkout & Pilihan Metode Pembayaran
    public function checkout(Request $request)
    {
        [$table, $tableRecord] = $this->resolveTable($request);
        $setting = RestaurantSetting::getSettings();
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('menu')->with('error', 'Keranjang belanja masih kosong.');
        }

        // Verify stock for all cart items before proceeding
        foreach ($cart as $c) {
            $product = Product::find($c['product_id'] ?? null);
            if (!$product || ($product->track_stock && $product->stock < $c['quantity'])) {
                $productName = $product ? $product->name : ($c['name'] ?? 'Menu');
                $sisa = $product ? $product->stock : 0;
                $msg = $sisa <= 0
                    ? "Maaf, stok {$productName} telah habis. Silakan hapus atau sesuaikan pesanan di keranjang."
                    : "Maaf, sisa stok {$productName} hanya {$sisa} porsi. Silakan sesuaikan jumlah pesanan Anda.";
                return redirect()->route('cart')->with('error', $msg);
            }
        }

        $subtotal = 0;
        $totalItems = 0;
        foreach ($cart as $c) {
            $subtotal += $c['price'] * $c['quantity'];
            $totalItems += $c['quantity'];
        }

        $serviceFee = (int) round($subtotal * ($setting->service_fee_pct / 100));
        $pbjt = (int) round($subtotal * ($setting->pbjt_tax_pct / 100));
        $grandTotal = $subtotal + $serviceFee + $pbjt;

        $orderNumber = '#SP-' . str_pad(rand(100, 999), 4, '0', STR_PAD_LEFT);

        return view('checkout.index', compact('table', 'cart', 'subtotal', 'totalItems', 'serviceFee', 'pbjt', 'grandTotal', 'orderNumber', 'setting'));
    }

    // Layar 6: Status Pesanan Real-time (Tracking 7 Tahap)
    public function orderTracking(Request $request)
    {
        [$table, $tableRecord] = $this->resolveTable($request);
        $orderId = $request->get('order_id', session('active_order_id'));

        $order = null;
        if ($orderId) {
            $order = Order::with(['items.product', 'payment'])->find($orderId);
        }
        if (!$order) {
            $order = Order::where('table_number', $table)->with(['items.product', 'payment'])->latest()->first();
        }
        if (!$order) {
            $order = Order::with(['items.product', 'payment'])->latest()->first();
        }

        return view('orders.status', compact('table', 'order'));
    }

    // Struk Digital Download / Cetak
    public function receipt($orderNumber)
    {
        $normalized = '#' . ltrim(urldecode($orderNumber), '#');
        $order = Order::where('order_number', $normalized)
            ->orWhere('order_number', urldecode($orderNumber))
            ->orWhere('order_number', $orderNumber)
            ->with(['items', 'table'])
            ->firstOrFail();
        $setting = RestaurantSetting::getSettings();

        return view('orders.receipt', compact('order', 'setting'));
    }

    // Real-Time Polling Endpoint untuk Customer (Live Status Update tanpa reload)
    public function streamStatus($orderNumber)
    {
        $normalized = '#' . ltrim(urldecode($orderNumber), '#');
        $order = Order::where('order_number', $normalized)
            ->orWhere('order_number', urldecode($orderNumber))
            ->orWhere('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json([
            'order_number' => $order->order_number,
            'table_number' => $order->table_number,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'payment_status_label' => $order->payment_status_label,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'eta_range' => $order->eta_range,
            'payment_time' => $order->payment_time,
            'cooking_time' => $order->cooking_time,
            'is_paid' => $order->isPaid(),
        ]);
    }
}
