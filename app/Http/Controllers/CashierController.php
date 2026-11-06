<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierController extends Controller
{
    /**
     * Cashier Dashboard: Order management and live queue
     */
    public function dashboard(Request $request)
    {
        $statusFilter = $request->get('status', 'all');
        $paymentFilter = $request->get('payment', 'all');
        $search = trim($request->get('q', ''));

        $query = Order::with(['items.product', 'payment', 'cashier'])->latest();

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($paymentFilter !== 'all') {
            $query->where('payment_status', $paymentFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('table_number', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        // Counts for tabs & badges
        $counts = [
            'total' => Order::whereDate('created_at', today())->count(),
            'waiting_cash' => Order::where('payment_method', 'cash')->where('payment_status', 'pending')->count(),
            'received' => Order::where('status', 'received')->count(),
            'cooking' => Order::where('status', 'cooking')->count(),
            'preparing' => Order::where('status', 'preparing')->count(),
            'delivering' => Order::where('status', 'delivering')->count(),
            'completed' => Order::where('status', 'completed')->whereDate('updated_at', today())->count(),
        ];

        return view('cashier.dashboard', compact('orders', 'counts', 'statusFilter', 'paymentFilter', 'search'));
    }

    /**
     * API for Live Real-Time Order Polling and Audio Notification Trigger
     */
    public function liveOrders(Request $request)
    {
        $lastCheckedId = $request->input('last_id', 0);

        // Check if there are new orders created after lastCheckedId
        $newOrders = Order::with(['items'])
            ->where('id', '>', $lastCheckedId)
            ->latest()
            ->get();

        $activeOrders = Order::with(['items', 'payment'])
            ->whereIn('status', ['waiting_payment', 'paid', 'received', 'cooking', 'preparing', 'delivering'])
            ->latest()
            ->get();

        $latestId = Order::max('id') ?? 0;

        return response()->json([
            'latest_id' => $latestId,
            'has_new' => $newOrders->isNotEmpty() && $lastCheckedId > 0,
            'new_count' => $newOrders->count(),
            'active_orders' => $activeOrders,
            'summary' => [
                'waiting_cash' => Order::where('payment_method', 'cash')->where('payment_status', 'pending')->count(),
                'in_progress' => Order::whereIn('status', ['received', 'cooking', 'preparing', 'delivering'])->count(),
            ],
        ]);
    }

    /**
     * Cashier confirms manual Cash payment from Customer
     */
    public function confirmCash(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->payment_method !== 'cash') {
            return redirect()->back()->with('error', 'Pesanan ini bukan metode pembayaran tunai.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->back()->with('info', 'Pesanan ini sudah lunas sebelumnya.');
        }

        $now = now();
        $order->update([
            'payment_status' => 'paid',
            'status' => 'received', // Maju otomatis ke tahap 3: Pesanan diterima
            'paid_at' => $now,
            'payment_time' => $now->format('H.i'),
            'confirmed_by' => Auth::id(),
        ]);

        Payment::where('order_id', $order->id)->update([
            'status' => 'success',
        ]);

        PaymentLog::create([
            'order_id' => $order->id,
            'event' => 'cash_confirmed_by_cashier',
            'payload' => [
                'cashier_id' => Auth::id(),
                'cashier_name' => Auth::user()->name,
                'amount' => $order->total_amount,
            ],
            'ip_address' => $request->ip(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pembayaran tunai untuk {$order->order_number} berhasil dikonfirmasi.",
                'order' => $order,
            ]);
        }

        return redirect()->back()->with('success', "Pembayaran tunai {$order->order_number} ({$order->table_number}) berhasil dikonfirmasi! Pesanan diteruskan ke dapur.");
    }

    /**
     * Cashier updates order fulfillment stage (7 Stages)
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validStatuses = [
            'waiting_payment',
            'paid',
            'received',
            'cooking',
            'preparing',
            'delivering',
            'completed',
            'cancelled',
        ];

        $request->validate([
            'status' => 'required|in:' . implode(',', $validStatuses),
        ]);

        $newStatus = $request->input('status');
        $order->status = $newStatus;

        if ($newStatus === 'cooking' && empty($order->cooking_time)) {
            $order->cooking_time = now()->format('H.i');
        }

        $order->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status pesanan {$order->order_number} diperbarui menjadi {$order->status_label}.",
                'order' => $order,
            ]);
        }

        return redirect()->back()->with('success', "Status {$order->order_number} berhasil diubah ke: {$order->status_label}.");
    }

    /**
     * View detailed modal/information for a single order
     */
    public function showOrder($id)
    {
        $order = Order::with(['items.product', 'payment', 'cashier'])->findOrFail($id);

        return response()->json($order);
    }
}

