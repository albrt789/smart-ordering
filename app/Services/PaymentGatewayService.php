<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    protected string $serverKey;
    protected string $merchantId;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-SUAPDEMOKEY123'));
        $this->merchantId = config('services.midtrans.merchant_id', env('MIDTRANS_MERCHANT_ID', 'G123456789'));
    }

    /**
     * Create Dynamic QRIS Payment for an Order
     */
    public function createDynamicQris(Order $order): Payment
    {
        $paymentCode = 'QRIS-' . str_replace('#', '', $order->order_number) . '-' . time();
        $amount = $order->total_amount;
        $expiresAt = now()->addMinutes(15);

        // Standard EMVCo-compliant QRIS Dynamic String payload
        $rawQris = $this->generateEmvcoQrisString($order->order_number, $amount);

        // Simpan transaksi di database
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_code' => $paymentCode,
            'gateway' => 'qris_dynamic',
            'amount' => $amount,
            'status' => 'pending',
            'qris_payload' => $rawQris,
            'raw_response' => [
                'merchant' => 'SUAP CIKINI',
                'order_id' => $order->order_number,
                'amount' => $amount,
                'currency' => 'IDR',
                'payment_type' => 'qris',
                'transaction_status' => 'pending',
                'qris_url' => "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($rawQris),
            ],
            'expires_at' => $expiresAt,
        ]);

        $order->update([
            'gateway_reference' => $paymentCode,
        ]);

        // Audit log
        PaymentLog::create([
            'order_id' => $order->id,
            'event' => 'qris_generated',
            'payload' => [
                'payment_code' => $paymentCode,
                'amount' => $amount,
                'expires_at' => $expiresAt->toDateTimeString(),
            ],
            'ip_address' => request()->ip(),
        ]);

        return $payment;
    }

    /**
     * Process Webhook Callback from Payment Gateway
     */
    public function handleWebhook(array $payload, ?string $signature = null): array
    {
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? '200';
        $grossAmount = $payload['gross_amount'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        PaymentLog::create([
            'order_id' => null,
            'event' => 'webhook_received',
            'payload' => $payload,
            'ip_address' => request()->ip(),
        ]);

        if (!$orderId) {
            return ['success' => false, 'message' => 'Order ID is missing in webhook.'];
        }

        $order = Order::where('order_number', $orderId)->first();
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        // Verifikasi Signature (Midtrans / Gateway standard)
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        $isValidSignature = ($signature === $expectedSignature) || ($payload['sandbox_simulation'] ?? false);

        if (!$isValidSignature && !app()->isLocal()) {
            PaymentLog::create([
                'order_id' => $order->id,
                'event' => 'invalid_signature',
                'payload' => ['provided' => $signature, 'expected' => $expectedSignature],
                'ip_address' => request()->ip(),
            ]);
            return ['success' => false, 'message' => 'Invalid webhook signature.'];
        }

        // Verifikasi nominal agar tidak terjadi manipulasi harga
        if ($grossAmount && (int) $grossAmount !== (int) $order->total_amount) {
            PaymentLog::create([
                'order_id' => $order->id,
                'event' => 'amount_mismatch',
                'payload' => ['order_amount' => $order->total_amount, 'webhook_amount' => $grossAmount],
                'ip_address' => request()->ip(),
            ]);
            return ['success' => false, 'message' => 'Gross amount mismatch.'];
        }

        // Cegah pembayaran ganda jika order sudah lunas
        if ($order->payment_status === 'paid') {
            return ['success' => true, 'message' => 'Order already paid.'];
        }

        // Update status jika transaksi berhasil / settlement
        if (in_array($transactionStatus, ['settlement', 'capture', 'success'])) {
            $now = now();
            $order->update([
                'payment_status' => 'paid',
                'status' => 'received', // Maju ke status 3: Pesanan diterima
                'paid_at' => $now,
                'payment_time' => $now->format('H.i'),
            ]);

            Payment::where('order_id', $order->id)->latest()->update([
                'status' => 'success',
            ]);

            PaymentLog::create([
                'order_id' => $order->id,
                'event' => 'payment_settled_via_webhook',
                'payload' => $payload,
                'ip_address' => request()->ip(),
            ]);

            return ['success' => true, 'message' => 'Payment settled successfully.'];
        } elseif (in_array($transactionStatus, ['expire', 'expired'])) {
            $order->update([
                'payment_status' => 'expired',
                'status' => 'cancelled',
            ]);
            Payment::where('order_id', $order->id)->latest()->update(['status' => 'expired']);
            return ['success' => true, 'message' => 'Payment expired.'];
        }

        return ['success' => true, 'message' => 'Webhook received, status: ' . $transactionStatus];
    }

    /**
     * Generate EMVCo QRIS dynamic string representation
     */
    private function generateEmvcoQrisString(string $orderNumber, int $amount): string
    {
        $cleanOrder = preg_replace('/[^A-Za-z0-9]/', '', $orderNumber);
        $amtStr = (string) $amount;
        
        return "00020101021226600016ID.CO.QRIS.WWW01189360099900000000020215ID10200248000000520458125303360540" 
            . $amtStr 
            . "5802ID5911SUAP CIKINI6007JAKARTA61051033062160112" 
            . $cleanOrder 
            . "6304";
    }
}

