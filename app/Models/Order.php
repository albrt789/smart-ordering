<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // Helper to check if payment is confirmed
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getOrderTimeAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('H.i') : now()->format('H.i');
    }

    // Human-readable status label in Indonesian
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'waiting_payment' => 'Menunggu Pembayaran',
            'paid' => 'Pembayaran Berhasil',
            'received' => 'Pesanan Diterima',
            'cooking' => 'Sedang Dibuat',
            'preparing' => 'Sedang Disiapkan',
            'delivering' => 'Sedang Diantarkan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'pending' => 'Belum Lunas',
            'paid' => 'Lunas',
            'failed' => 'Gagal',
            'expired' => 'Kadaluarsa',
            default => $this->payment_status,
        };
    }
}
