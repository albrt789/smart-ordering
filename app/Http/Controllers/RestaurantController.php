<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    // Helper to get active table from request or session
    private function getTable(Request $request)
    {
        if ($request->has('meja')) {
            $t = $request->get('meja');
            $tableName = str_starts_with($t, 'Meja') ? $t : 'Meja ' . str_pad($t, 2, '0', STR_PAD_LEFT);
            session(['current_table' => $tableName]);
            return $tableName;
        }
        return session('current_table', 'Meja 08');
    }

    // Layar 1: Menu Utama
    public function index(Request $request)
    {
        $table = $this->getTable($request);
        $category = $request->get('category', 'Semua');
        $search = $request->get('q', '');

        $query = MenuItem::query();

        if ($category && $category !== 'Semua') {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $menuItems = $query->get();
        $categories = ['Semua', 'Makanan', 'Camilan', 'Minuman'];
        
        $cart = session('cart', $this->getDefaultCart());
        $cartCount = array_sum(array_column($cart, 'quantity'));
        $subtotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));

        return view('menu.index', compact('menuItems', 'categories', 'category', 'search', 'table', 'cart', 'cartCount', 'subtotal'));
    }

    // Layar 2: Detail Makanan
    public function show($slug, Request $request)
    {
        $table = $this->getTable($request);
        $dish = MenuItem::where('slug', $slug)->firstOrFail();
        return view('menu.show', compact('dish', 'table'));
    }

    // Layar 4: Keranjang Pesanan
    public function cart(Request $request)
    {
        $table = $this->getTable($request);
        $cart = session('cart', $this->getDefaultCart());

        $subtotal = 0;
        $totalItems = 0;
        foreach ($cart as $c) {
            $subtotal += $c['price'] * $c['quantity'];
            $totalItems += $c['quantity'];
        }

        $serviceFee = (int) round($subtotal * 0.05); // 5%
        $pbjt = (int) round($subtotal * 0.10); // 10%
        $grandTotal = $subtotal + $serviceFee + $pbjt;

        return view('cart.index', compact('table', 'cart', 'subtotal', 'totalItems', 'serviceFee', 'pbjt', 'grandTotal'));
    }

    // Tambah ke keranjang
    public function addToCart(Request $request)
    {
        $dishId = $request->input('dish_id');
        $dish = MenuItem::findOrFail($dishId);
        $quantity = (int) $request->input('quantity', 1);
        $note = $request->input('note', '');
        
        $selectedOptions = $request->input('options', []);
        $optionsSummary = [];
        $extraPrice = 0;

        if (!empty($selectedOptions) && is_array($selectedOptions)) {
            foreach ($selectedOptions as $optId => $choiceName) {
                $optionsSummary[] = $choiceName;
                if ($dish->options) {
                    foreach ($dish->options as $opt) {
                        if ($opt['id'] === $optId) {
                            foreach ($opt['choices'] as $ch) {
                                if ($ch['name'] === $choiceName && !empty($ch['extraPrice'])) {
                                    $extraPrice += $ch['extraPrice'];
                                }
                            }
                        }
                    }
                }
            }
        }

        $cart = session('cart', $this->getDefaultCart());
        $cartItemId = $dish->id . '-' . time() . '-' . rand(100, 999);

        $cart[$cartItemId] = [
            'id' => $cartItemId,
            'dish_id' => $dish->id,
            'slug' => $dish->slug,
            'name' => $dish->name,
            'image' => $dish->image,
            'price' => $dish->price + $extraPrice,
            'quantity' => $quantity,
            'options' => implode(' · ', $optionsSummary),
            'note' => $note,
            'is_ai_pick' => $request->boolean('is_ai_pick', false),
        ];

        session(['cart' => $cart]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'cart' => $cart]);
        }

        return redirect()->route('cart');
    }

    // Update Quantity di keranjang
    public function updateCart(Request $request)
    {
        $id = $request->input('id');
        $delta = (int) $request->input('delta', 1);

        $cart = session('cart', $this->getDefaultCart());

        if (isset($cart[$id])) {
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

    // Layar 5: Checkout dan Pembayaran QRIS
    public function checkout(Request $request)
    {
        $table = $this->getTable($request);
        $cart = session('cart', $this->getDefaultCart());

        $subtotal = 0;
        $totalItems = 0;
        foreach ($cart as $c) {
            $subtotal += $c['price'] * $c['quantity'];
            $totalItems += $c['quantity'];
        }

        $serviceFee = (int) round($subtotal * 0.05); // 5%
        $pbjt = (int) round($subtotal * 0.10); // 10%
        $grandTotal = $subtotal + $serviceFee + $pbjt;

        $orderNumber = '#SP-' . str_pad(rand(100, 999), 4, '0', STR_PAD_LEFT);

        return view('checkout.index', compact('table', 'cart', 'subtotal', 'totalItems', 'serviceFee', 'pbjt', 'grandTotal', 'orderNumber'));
    }

    // Proses Pembayaran QRIS & Simpan Order
    public function processPayment(Request $request)
    {
        $table = $this->getTable($request);
        $customerName = $request->input('customer_name', 'Dina');
        $paymentMethod = $request->input('payment_method', 'QRIS');

        $cart = session('cart', $this->getDefaultCart());

        $subtotal = 0;
        $orderItems = [];
        foreach ($cart as $c) {
            $itemTotal = $c['price'] * $c['quantity'];
            $subtotal += $itemTotal;
            $orderItems[] = [
                'name' => $c['name'],
                'quantity' => $c['quantity'],
                'price' => $itemTotal,
                'options' => $c['options'] ?? '',
                'note' => $c['note'] ?? ''
            ];
        }

        $serviceFee = (int) round($subtotal * 0.05);
        $pbjt = (int) round($subtotal * 0.10);
        $grandTotal = $subtotal + $serviceFee + $pbjt;

        $now = now();
        $etaStart = $now->copy()->addMinutes(20)->format('H.i');
        $etaEnd = $now->copy()->addMinutes(25)->format('H.i');
        $orderNum = '#SP-' . str_pad(rand(100, 999), 4, '0', STR_PAD_LEFT);

        $order = Order::create([
            'order_number' => $orderNum,
            'table_number' => $table,
            'customer_name' => $customerName,
            'status' => 'cooking',
            'payment_method' => $paymentMethod,
            'payment_status' => 'LUNAS',
            'items' => $orderItems,
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'pbjt' => $pbjt,
            'grand_total' => $grandTotal,
            'eta_range' => "{$etaStart}–{$etaEnd}",
            'payment_time' => $now->format('H.i'),
            'cooking_time' => $now->copy()->addMinutes(2)->format('H.i'),
        ]);

        // Clear cart
        session()->forget('cart');
        session(['active_order_id' => $order->id]);

        return redirect()->route('orders.status', ['order_id' => $order->id]);
    }

    // Layar 6: Konfirmasi dan Status Pesanan
    public function orderStatus(Request $request)
    {
        $table = $this->getTable($request);
        $orderId = $request->get('order_id', session('active_order_id'));

        $order = null;
        if ($orderId) {
            $order = Order::find($orderId);
        }
        if (!$order) {
            $order = Order::where('table_number', $table)->latest()->first();
        }
        if (!$order) {
            $order = Order::latest()->first();
        }

        return view('orders.status', compact('table', 'order'));
    }

    // Kitchen Display System (KDS)
    public function kitchen()
    {
        $orders = Order::latest()->get();
        return view('kitchen.index', compact('orders'));
    }

    // Majukan status order dari dapur
    public function updateKitchenStatus($id, Request $request)
    {
        $order = Order::findOrFail($id);
        $newStatus = $request->input('status');
        $order->update(['status' => $newStatus]);

        return redirect()->route('kitchen');
    }

    // Table QR Generator & Manager
    public function tables(Request $request)
    {
        $table = $this->getTable($request);
        $tablesList = array_map(fn($i) => 'Meja ' . str_pad($i, 2, '0', STR_PAD_LEFT), range(1, 12));
        return view('tables.index', compact('table', 'tablesList'));
    }

    // Default Seed Cart matching Screenshot 1 & 4
    private function getDefaultCart()
    {
        return [
            'seed-1' => [
                'id' => 'seed-1',
                'dish_id' => 1,
                'slug' => 'ayam-sambal-matah',
                'name' => 'Ayam Sambal Matah',
                'image' => 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?auto=format&fit=crop&w=800&q=80',
                'price' => 42000,
                'quantity' => 1,
                'options' => 'Nasi putih · Sambal dipisah',
                'note' => 'Ayamnya dipotong kecil, ya.',
                'is_ai_pick' => false
            ],
            'seed-2' => [
                'id' => 'seed-2',
                'dish_id' => 2,
                'slug' => 'jamur-crispy',
                'name' => 'Jamur Crispy',
                'image' => 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=800&q=80',
                'price' => 24000,
                'quantity' => 1,
                'options' => 'Saus cocol terpisah',
                'note' => '',
                'is_ai_pick' => true
            ],
            'seed-3' => [
                'id' => 'seed-3',
                'dish_id' => 3,
                'slug' => 'es-teh-leci',
                'name' => 'Es Teh Leci',
                'image' => 'https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&w=800&q=80',
                'price' => 22000,
                'quantity' => 1,
                'options' => 'Gula normal · Es normal',
                'note' => '',
                'is_ai_pick' => false
            ]
        ];
    }
}

