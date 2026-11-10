<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'service_fee_pct' => 'decimal:2',
        'pbjt_tax_pct' => 'decimal:2',
    ];

    public static function getSettings()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Fountain Hide',
                'branch' => 'Fountain Hide Coffee',
                'address' => 'Jl. Tirta Bhakti No. 23C',
                'phone' => '0812-8800-4499',
                'email' => 'hello@fountainhide.com',
                'open_hours' => '08:00 - 23:00 WIB',
                'service_fee_pct' => 5.00,
                'pbjt_tax_pct' => 10.00,
                'description' => 'Specialty coffee roastery, artisanal brews, and signature coffee drinks. Nikmati pengalaman dine-in instan pesan langsung dari meja tanpa antre.',
                'logo' => '/images/logo.png',
            ]
        );
    }
}

