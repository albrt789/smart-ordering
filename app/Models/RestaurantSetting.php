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
                'name' => 'SUAP.',
                'branch' => 'Suap, Cikini',
                'address' => 'Jl. Cikini Raya No. 45, Menteng, Jakarta Pusat',
                'phone' => '0812-9876-5432',
                'email' => 'halo@suap.id',
                'open_hours' => '10:00 - 22:00 WIB',
                'service_fee_pct' => 5.00,
                'pbjt_tax_pct' => 10.00,
                'description' => 'Restoran modern menyajikan hidangan lokal Nusantara bercita rasa otentik dengan pelayanan pesan instan dari meja.'
            ]
        );
    }
}

