<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected PaymentGatewayService $paymentService;

    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Process order checkout from customer
     */
    public function processCheckout(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:100',
            'payment_method' => 'required|in:qris,cash',
            'notes' => 'nullable|string|max:500',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Keranjang pesanan masih kosong.');
        }

        $tableName = session('current_table', 'Meja 08');
        if ($request->has('table_number')) {
            $tableName = $request->input('table_number');
        }

        $setting = RestaurantSetting::getSettings();

        DB::beginTransaction();
        try {
            // Server-side verification of cart pricing and real-time stock
            $subtotal = 0;
            $orderItemsData = [];
            $productsToDecrement = [];

            foreach ($cart as $itemKey => $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                $product = Product::with('options.choices')->lockForUpdate()->find($item['product_id']);
                if (!$product) {
                    continue;
                }

                // Check real-time stock availability
                if ($product->track_stock && ($product->stock < $qty)) {
                    DB::rollBack();
                    $sisaMsg = $product->stock <= 0
                        ? "Maaf, stok menu '{$product->name}' baru saja habis. Silakan periksa kembali keranjang Anda."
                        : "Maaf, sisa stok '{$product->name}' hanya {$product->stock} porsi (pesanan Anda: {$qty} porsi).";
                    return redirect()->route('cart')->with('error', $sisaMsg);
                }

                // Verify base price + modifiers
                $itemBasePrice = $product->price;
                $optionsPrice = 0;
                $optionsList = $item['options'] ?? [];

                if (!empty($optionsList)) {
                    foreach ($product->options as $opt) {
                        foreach ($opt->choices as $choice) {
                            if (in_array($choice->name, $optionsList)) {
                                $optionsPrice += $choice->extra_price;
                            }
                        }
                    }
                }

                $verifiedUnitPrice = $itemBasePrice + $optionsPrice;
                $lineSubtotal = $verifiedUnitPrice * $qty;
                $subtotal += $lineSubtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $verifiedUnitPrice,
                    'quantity' => $qty,
                    'subtotal' => $lineSubtotal,
                    'options_selected' => $optionsList,
                    'kitchen_note' => $item['note'] ?? null,
                ];

                $productsToDecrement[] = [
                    'product' => $product,
                    'quantity' => $qty,
                ];
            }

            if (empty($orderItemsData)) {
                DB::rollBack();
                return redirect()->route('cart')->with('error', 'Produk dalam keranjang tidak valid.');
            }

            // Decrement stock for tracked products
            foreach ($productsToDecrement as $dec) {
                if ($dec['product']->track_stock) {
                    $dec['product']->decrement('stock', $dec['quantity']);
                }
            }

            // Calculate fees & taxes
            $serviceFee = (int) round($subtotal * ($setting->service_fee_pct / 100));
            $pbjtTax = (int) round($subtotal * ($setting->pbjt_tax_pct / 100));
            $grandTotal = $subtotal + $serviceFee + $pbjtTax;

            // Generate unique Order Number
            $orderNumber = '#SP-' . str_pad((string) rand(100, 9999), 4, '0', STR_PAD_LEFT);
            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = '#SP-' . str_pad((string) rand(100, 9999), 4, '0', STR_PAD_LEFT);
            }

            $paymentMethod = $request->input('payment_method');
            $now = now();
            $etaRange = $now->copy()->addMinutes(15)->format('H.i') . '-' . $now->copy()->addMinutes(25)->format('H.i');

            // 1. Create Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_name' => trim($request->input('customer_name')),
                'table_number' => $tableName,
                'subtotal' => $subtotal,
                'service_fee' => $serviceFee,
                'pbjt_tax' => $pbjtTax,
                'total_amount' => $grandTotal,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'status' => 'waiting_payment',
                'eta_range' => $etaRange,
            ]);

            // 2. Create Order Items
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // 3. Setup Payment Record
            if ($paymentMethod === 'qris') {
                $this->paymentService->createDynamicQris($order);
            } else {
                // Cash Payment option
                Payment::create([
                    'order_id' => $order->id,
                    'payment_code' => 'CASH-' . str_replace('#', '', $orderNumber) . '-' . time(),
                    'gateway' => 'cash',
                    'amount' => $grandTotal,
                    'status' => 'pending',
                    'raw_response' => [
                        'type' => 'cash',
                        'instructions' => 'Silakan lakukan pembayaran tunai di meja kasir.',
                    ],
                ]);
            }

            DB::commit();

            // Clear shopping cart
            session()->forget('cart');
            session(['active_order_id' => $order->id, 'active_order_number' => $order->order_number]);

            return redirect()->route('orders.status', ['order_id' => $order->id])
                ->with('success', 'Pesanan berhasil dibuat! ' . ($paymentMethod === 'qris' ? 'Silakan scan QRIS untuk menyelesaikan pembayaran.' : 'Silakan lakukan pembayaran tunai di kasir.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('checkout')->with('error', 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Webhook Handler for Payment Gateway Notifications
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Signature') ?? $request->input('signature_key');

        $result = $this->paymentService->handleWebhook($payload, $signature);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Sandbox Simulation: Instantly confirm QRIS payment
     */
    public function simulatePayment($orderNumber, Request $request)
    {
        $normalized = '#' . ltrim(urldecode($orderNumber), '#');
        $order = Order::where('order_number', $normalized)
            ->orWhere('order_number', urldecode($orderNumber))
            ->orWhere('order_number', $orderNumber)
            ->first();

        if (!$order) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        if ($order->payment_status === 'paid') {
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Pesanan sudah lunas.']);
            }
            return redirect()->back()->with('info', 'Pesanan sudah lunas.');
        }

        $now = now();
        $order->update([
            'payment_status' => 'paid',
            'status' => 'received', // Maju ke status 3: Pesanan Diterima Dapur
            'paid_at' => $now,
            'payment_time' => $now->format('H.i'),
        ]);

        Payment::where('order_id', $order->id)->update([
            'status' => 'success',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran QRIS berhasil dikonfirmasi secara instan.',
                'order' => $order,
            ]);
        }

        return redirect()->route('orders.status', ['order_id' => $order->id])
            ->with('success', 'Pembayaran QRIS berhasil dikonfirmasi!');
    }
}
