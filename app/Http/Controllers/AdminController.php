<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Admin Dashboard: Statistics, charts, best sellers, and summaries
     */
    public function dashboard()
    {
        $today = today();
        $thisMonth = now()->startOfMonth();

        // 1. Core KPIs
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $todayRevenue = Order::where('payment_status', 'paid')->whereDate('paid_at', $today)->sum('total_amount');
        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('created_at', $today)->count();

        // 2. Best Selling Menus
        $bestSellers = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // 3. Payment Method Distribution (QRIS vs Cash)
        $qrisRevenue = Order::where('payment_status', 'paid')->where('payment_method', 'qris')->sum('total_amount');
        $cashRevenue = Order::where('payment_status', 'paid')->where('payment_method', 'cash')->sum('total_amount');

        // 4. Recent Transactions
        $recentOrders = Order::with(['items', 'payment'])->latest()->limit(8)->get();

        // 5. 7-Day Sales Trend
        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayName = now()->subDays($i)->locale('id')->isoFormat('ddd');
            $rev = Order::where('payment_status', 'paid')->whereDate('paid_at', $date)->sum('total_amount');
            $cnt = Order::whereDate('created_at', $date)->count();
            $salesTrend[] = [
                'date' => $date,
                'label' => $dayName,
                'revenue' => (int) $rev,
                'count' => $cnt,
            ];
        }

        return view('admin.dashboard', compact(
            'totalRevenue',
            'todayRevenue',
            'totalOrders',
            'todayOrders',
            'bestSellers',
            'qrisRevenue',
            'cashRevenue',
            'recentOrders',
            'salesTrend'
        ));
    }

    // ==========================================
    // MENU MANAGEMENT (CRUD)
    // ==========================================

    public function menuIndex(Request $request)
    {
        $categoryFilter = $request->get('category', 'all');
        $search = trim($request->get('q', ''));

        $query = Product::with('category')->latest();

        if ($categoryFilter !== 'all') {
            $query->where('category_id', $categoryFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('composition', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::withCount('products')->orderBy('sort_order')->get();

        return view('admin.menu.index', compact('products', 'categories', 'categoryFilter', 'search'));
    }

    public function menuCreate()
    {
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.menu.form', [
            'product' => new Product(),
            'categories' => $categories,
            'isEdit' => false,
        ]);
    }

    public function menuStore(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:150',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'composition' => 'nullable|string',
            'allergens' => 'nullable|string',
            'portion_desc' => 'nullable|string|max:100',
            'prep_time_minutes' => 'required|integer|min:1',
            'spice_level' => 'nullable|string',
            'badge' => 'nullable|string|max:50',
            'stock' => 'nullable|integer|min:0',
            'daily_stock' => 'nullable|integer|min:0',
            'track_stock' => 'nullable|boolean',
            'is_signature' => 'boolean',
            'is_available' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['image', 'is_signature', 'is_available', 'track_stock']);
        $data['slug'] = Str::slug($request->name) . '-' . rand(10, 99);
        $data['is_signature'] = $request->boolean('is_signature', false);
        $data['is_available'] = $request->boolean('is_available', true);
        $data['track_stock'] = $request->boolean('track_stock', true);
        $data['daily_stock'] = (int) $request->input('daily_stock', 50);
        $data['stock'] = $request->has('stock') ? (int) $request->input('stock') : $data['daily_stock'];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = '/storage/' . $path;
        } else {
            $data['image'] = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&auto=format&fit=crop&q=80';
        }

        Product::create($data);

        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil ditambahkan!');
    }

    public function menuEdit($id)
    {
        $product = Product::with(['options.choices'])->findOrFail($id);
        $categories = Category::orderBy('sort_order')->get();

        return view('admin.menu.form', [
            'product' => $product,
            'categories' => $categories,
            'isEdit' => true,
        ]);
    }

    public function menuUpdate(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:150',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'composition' => 'nullable|string',
            'allergens' => 'nullable|string',
            'portion_desc' => 'nullable|string|max:100',
            'prep_time_minutes' => 'required|integer|min:1',
            'spice_level' => 'nullable|string',
            'badge' => 'nullable|string|max:50',
            'stock' => 'nullable|integer|min:0',
            'daily_stock' => 'nullable|integer|min:0',
            'track_stock' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['image', 'is_signature', 'is_available', 'track_stock']);
        $data['is_signature'] = $request->boolean('is_signature', false);
        $data['is_available'] = $request->boolean('is_available', false);
        $data['track_stock'] = $request->boolean('track_stock', false);

        if ($request->has('daily_stock')) {
            $data['daily_stock'] = (int) $request->input('daily_stock');
        }
        if ($request->has('stock')) {
            $data['stock'] = (int) $request->input('stock');
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = '/storage/' . $path;
        }

        $product->update($data);

        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil diperbarui!');
    }

    /**
     * Quick stock update (AJAX or direct form from Admin table)
     */
    public function updateStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'stock' => 'required|integer|min:0',
            'daily_stock' => 'nullable|integer|min:0',
            'track_stock' => 'nullable|boolean',
        ]);

        $updateData = [
            'stock' => (int) $request->input('stock'),
        ];

        if ($request->has('daily_stock')) {
            $updateData['daily_stock'] = (int) $request->input('daily_stock');
        }

        if ($request->has('track_stock')) {
            $updateData['track_stock'] = $request->boolean('track_stock');
        }

        $product->update($updateData);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Stok {$product->name} berhasil diperbarui menjadi {$product->stock} porsi.",
                'stock' => $product->stock,
                'daily_stock' => $product->daily_stock,
                'is_out_of_stock' => $product->isOutOfStock(),
            ]);
        }

        return back()->with('success', "Stok '{$product->name}' berhasil diperbarui (Sisa: {$product->stock} porsi).");
    }

    /**
     * Reset all tracked product stock to daily quota with 1 click
     */
    public function resetDailyStock(Request $request)
    {
        $count = Product::where('track_stock', true)->update([
            'stock' => DB::raw('daily_stock'),
        ]);

        return back()->with('success', "Stok seluruh menu ({$count} hidangan) berhasil di-reset sesuai kuota harian masing-masing!");
    }

    public function menuDestroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil dihapus!');
    }

    // ==========================================
    // CATEGORY MANAGEMENT
    // ==========================================

    public function categoryStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'sort_order' => 'nullable|integer',
        ]);

        Category::create([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->back()->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function categoryDestroy($id)
    {
        $category = Category::withCount('products')->findOrFail($id);
        if ($category->products_count > 0) {
            return redirect()->back()->with('error', 'Kategori ini tidak dapat dihapus karena masih memiliki produk terkait.');
        }

        $category->delete();
        return redirect()->back()->with('success', 'Kategori berhasil dihapus!');
    }

    // ==========================================
    // ORDER MANAGEMENT
    // ==========================================

    public function orderIndex(Request $request)
    {
        $status = $request->get('status', 'all');
        $paymentStatus = $request->get('payment_status', 'all');
        $dateFrom = $request->get('from');
        $dateTo = $request->get('to');
        $search = trim($request->get('q', ''));

        $query = Order::with(['items.product', 'payment', 'cashier'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('table_number', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders', 'status', 'paymentStatus', 'dateFrom', 'dateTo', 'search'));
    }

    // ==========================================
    // FINANCIAL REPORTS & EXPORT
    // ==========================================

    public function reports(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly, custom
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($period === 'daily') {
            $start = today()->startOfDay();
            $end = today()->endOfDay();
        } elseif ($period === 'weekly') {
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
        } elseif ($period === 'custom' && $startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $end = \Carbon\Carbon::parse($endDate)->endOfDay();
        } else { // monthly
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
        }

        $ordersQuery = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->with(['items', 'payment']);

        $totalRevenue = (clone $ordersQuery)->sum('total_amount');
        $subtotalAmount = (clone $ordersQuery)->sum('subtotal');
        $serviceFeeAmount = (clone $ordersQuery)->sum('service_fee');
        $pbjtTaxAmount = (clone $ordersQuery)->sum('pbjt_tax');
        $totalTransactions = (clone $ordersQuery)->count();

        $qrisTotal = (clone $ordersQuery)->where('payment_method', 'qris')->sum('total_amount');
        $cashTotal = (clone $ordersQuery)->where('payment_method', 'cash')->sum('total_amount');

        $orders = $ordersQuery->latest()->get();

        // Top Selling in Period
        $productSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end])
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.subtotal) as total'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty')
            ->get();

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($orders, $period, $start, $end);
        }

        if ($request->get('export') === 'print') {
            $setting = RestaurantSetting::getSettings();
            return view('admin.reports.print', compact(
                'orders', 'period', 'start', 'end', 'totalRevenue',
                'subtotalAmount', 'serviceFeeAmount', 'pbjtTaxAmount',
                'totalTransactions', 'qrisTotal', 'cashTotal', 'productSales', 'setting'
            ));
        }

        return view('admin.reports.index', compact(
            'period', 'start', 'end', 'totalRevenue', 'subtotalAmount',
            'serviceFeeAmount', 'pbjtTaxAmount', 'totalTransactions',
            'qrisTotal', 'cashTotal', 'orders', 'productSales'
        ));
    }

    private function exportCsv($orders, $period, $start, $end)
    {
        $fileName = 'Laporan-Keuangan-Fountain-Hide-' . $period . '-' . date('Ymd-His') . '.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['No. Pesanan', 'Tanggal & Waktu', 'Meja', 'Pelanggan', 'Metode Bayar', 'Status Bayar', 'Subtotal (Rp)', 'Layanan (Rp)', 'PBJT 10% (Rp)', 'Total (Rp)']);

            foreach ($orders as $o) {
                fputcsv($file, [
                    $o->order_number,
                    $o->created_at->format('d/m/Y H:i'),
                    $o->table_number,
                    $o->customer_name,
                    strtoupper($o->payment_method),
                    strtoupper($o->payment_status),
                    $o->subtotal,
                    $o->service_fee,
                    $o->pbjt_tax,
                    $o->total_amount,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // RESTAURANT MANAGEMENT (SETTINGS)
    // ==========================================

    public function settingsIndex()
    {
        $setting = RestaurantSetting::getSettings();
        return view('admin.settings.index', compact('setting'));
    }

    public function settingsUpdate(Request $request)
    {
        $setting = RestaurantSetting::getSettings();

        $request->validate([
            'name' => 'required|string|max:100',
            'branch' => 'required|string|max:100',
            'description' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|max:100',
            'open_hours' => 'required|string|max:100',
            'service_fee_pct' => 'required|numeric|min:0|max:100',
            'pbjt_tax_pct' => 'required|numeric|min:0|max:100',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['logo']);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            $data['logo'] = '/storage/' . $path;
        }

        $setting->update($data);

        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan restoran berhasil diperbarui!');
    }

    // ==========================================
    // USER MANAGEMENT (CASHIER ACCOUNTS)
    // ==========================================

    public function usersIndex()
    {
        $cashiers = User::where('role', 'cashier')->latest()->get();
        return view('admin.users.index', compact('cashiers'));
    }

    public function userStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => trim($request->name),
            'email' => trim($request->email),
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Akun kasir baru berhasil dibuat!');
    }

    public function userUpdate(Request $request, $id)
    {
        $user = User::where('role', 'cashier')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
            'is_active' => 'required|boolean',
        ]);

        $user->name = trim($request->name);
        $user->email = trim($request->email);
        $user->phone = $request->phone;
        $user->is_active = $request->boolean('is_active');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Akun kasir berhasil diperbarui!');
    }

    public function userDestroy($id)
    {
        $user = User::where('role', 'cashier')->findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Akun kasir berhasil dihapus!');
    }

    // ==========================================
    // TABLE QR CODE GENERATOR & MANAGEMENT
    // ==========================================

    public function tablesIndex()
    {
        $tables = Table::orderBy('id')->get();
        return view('admin.tables.index', compact('tables'));
    }

    public function tableStore(Request $request)
    {
        $request->validate([
            'table_number' => 'required|string|max:30|unique:tables,table_number',
            'capacity' => 'required|integer|min:1',
            'location' => 'nullable|string|max:50',
        ]);

        $tableNumber = trim($request->table_number);
        $code = Str::slug($tableNumber);
        $qrUrl = url('/?meja=' . urlencode($tableNumber));

        Table::create([
            'table_number' => $tableNumber,
            'code' => $code,
            'capacity' => $request->capacity,
            'location' => $request->location ?? 'Indoor Dining Area',
            'qr_payload' => $qrUrl,
            'is_active' => true,
        ]);

        return redirect()->route('admin.tables.index')->with('success', 'Meja baru berhasil ditambahkan!');
    }
}
