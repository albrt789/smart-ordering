<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductChoice;
use App\Models\ProductOption;
use App\Models\RestaurantSetting;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Users (Admin & Cashier)
        User::truncate();
        User::create([
            'name' => 'Admin Operasional',
            'email' => 'admin@suap.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'phone' => '081211112222',
        ]);

        User::create([
            'name' => 'Siti Kasir',
            'email' => 'kasir@suap.id',
            'password' => Hash::make('kasir123'),
            'role' => 'cashier',
            'is_active' => true,
            'phone' => '081233334444',
        ]);

        // 2. Restaurant Settings
        RestaurantSetting::truncate();
        RestaurantSetting::create([
            'name' => 'SUAP.',
            'branch' => 'Suap, Cikini',
            'address' => 'Jl. Cikini Raya No. 45, Menteng, Jakarta Pusat 10330',
            'phone' => '0812-9876-5432',
            'email' => 'halo@suap.id',
            'open_hours' => '10:00 - 22:00 WIB',
            'service_fee_pct' => 5.00,
            'pbjt_tax_pct' => 10.00,
            'description' => 'Restoran modern menyajikan hidangan lokal Nusantara bercita rasa otentik dengan pelayanan pesan instan dari meja tanpa antre.',
        ]);

        // 3. Tables (Meja 01 s/d Meja 12)
        Table::truncate();
        $tables = [];
        for ($i = 1; $i <= 12; $i++) {
            $num = str_pad($i, 2, '0', STR_PAD_LEFT);
            $tableObj = Table::create([
                'table_number' => "Meja {$num}",
                'qr_token' => "suap-table-{$num}-" . Str::random(8),
                'is_active' => true,
            ]);
            $tables[$num] = $tableObj;
        }

        // 4. Categories
        Category::truncate();
        $catMakanan = Category::create(['name' => 'Makanan', 'slug' => 'makanan', 'sort_order' => 1]);
        $catCamilan = Category::create(['name' => 'Camilan', 'slug' => 'camilan', 'sort_order' => 2]);
        $catMinuman = Category::create(['name' => 'Minuman', 'slug' => 'minuman', 'sort_order' => 3]);

        // 5. Products & Modifiers
        ProductChoice::truncate();
        ProductOption::truncate();
        Product::truncate();

        // 5.1 Ayam Sambal Matah
        $pAyam = Product::create([
            'category_id' => $catMakanan->id,
            'name' => 'Ayam Sambal Matah',
            'slug' => 'ayam-sambal-matah',
            'price' => 42000,
            'tag' => 'PALING LARIS',
            'category_path' => 'MAKANAN / SIGNATURE',
            'subtitle' => 'Nasi hangat, ayam panggang',
            'description' => 'Ayam panggang juicy, sambal matah segar, nasi hangat, dan lalapan. Satu piring, penuh rasa.',
            'composition' => 'Daging paha ayam panggang tanpa tulang, sambal matah (bawang merah, cabai rawit merah, serai, daun jeruk, terasi udang, minyak kelapa murni, perasan jeruk limau), nasi putih pulen, selada, mentimun.',
            'image' => 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Pedas sedang', '1 porsi', '15-20 menit'],
            'allergens' => 'Mengandung kedelai & udang (terasi). Hubungi Suap AI atau staf kami untuk info alergi.',
            'is_signature' => true,
            'is_available' => true,
        ]);
        $optNasi = ProductOption::create(['product_id' => $pAyam->id, 'name' => 'Pilih nasi', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optNasi->id, 'name' => 'Nasi putih', 'extra_price' => 0, 'is_default' => true]);
        ProductChoice::create(['product_option_id' => $optNasi->id, 'name' => 'Nasi merah', 'extra_price' => 4000]);

        $optSambal = ProductOption::create(['product_id' => $pAyam->id, 'name' => 'Sambalnya gimana?', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optSambal->id, 'name' => 'Sambal dicampur', 'extra_price' => 0]);
        ProductChoice::create(['product_option_id' => $optSambal->id, 'name' => 'Sambal dipisah', 'extra_price' => 0, 'is_default' => true]);

        // 5.2 Jamur Crispy
        $pJamur = Product::create([
            'category_id' => $catCamilan->id,
            'name' => 'Jamur Crispy',
            'slug' => 'jamur-crispy',
            'price' => 24000,
            'tag' => 'NABATI',
            'category_path' => 'CAMILAN / VEGAN FRIENDLY',
            'subtitle' => 'Jamur tiram, saus cocol',
            'description' => 'Jamur tiram renyah, saus cocol terpisah. Pas untuk berbagi sambil menunggu.',
            'composition' => 'Jamur tiram segar pilihan, adonan tepung terigu bumbu gurih rempah, saus asam manis pedas racikan khas Suap. Bebas susu dan bebas kacang.',
            'image' => 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Gurih renyah', '1 porsi', '5-10 menit'],
            'allergens' => 'Mengandung gluten dari tepung terigu. Adonan tanpa susu dan kacang.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $optSaus = ProductOption::create(['product_id' => $pJamur->id, 'name' => 'Pilihan Saus', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optSaus->id, 'name' => 'Saus cocol terpisah', 'extra_price' => 0, 'is_default' => true]);
        ProductChoice::create(['product_option_id' => $optSaus->id, 'name' => 'Saus langsung dilumuri', 'extra_price' => 0]);
        ProductChoice::create(['product_option_id' => $optSaus->id, 'name' => 'Tambah saus keju', 'extra_price' => 3000]);

        // 5.3 Es Teh Leci
        $pTeh = Product::create([
            'category_id' => $catMinuman->id,
            'name' => 'Es Teh Leci',
            'slug' => 'es-teh-leci',
            'price' => 22000,
            'tag' => 'SEGAR',
            'category_path' => 'MINUMAN / BEST SELLER',
            'subtitle' => 'Teh melati & leci asli',
            'description' => 'Teh melati wangi dengan buah leci asli dan sensasi manis segar pelepas dahaga.',
            'composition' => 'Seduhan teh melati premium, sari buah leci asli, 2 butir buah leci utuh, sirup gula tebu murni, daun mint, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Dingin segar', 'Reguler', '3-5 menit'],
            'allergens' => 'Bebas alergen utama. Mengandung pemanis gula tebu alami.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $optGula = ProductOption::create(['product_id' => $pTeh->id, 'name' => 'Tingkat Gula', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optGula->id, 'name' => 'Gula normal', 'extra_price' => 0, 'is_default' => true]);
        ProductChoice::create(['product_option_id' => $optGula->id, 'name' => 'Less sugar (50%)', 'extra_price' => 0]);
        ProductChoice::create(['product_option_id' => $optGula->id, 'name' => 'Tanpa gula', 'extra_price' => 0]);

        $optEs = ProductOption::create(['product_id' => $pTeh->id, 'name' => 'Jumlah Es', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optEs->id, 'name' => 'Es normal', 'extra_price' => 0, 'is_default' => true]);
        ProductChoice::create(['product_option_id' => $optEs->id, 'name' => 'Less ice', 'extra_price' => 0]);
        ProductChoice::create(['product_option_id' => $optEs->id, 'name' => 'Tanpa es', 'extra_price' => 0]);

        // 5.4 Mie Ayam Suap
        $pMie = Product::create([
            'category_id' => $catMakanan->id,
            'name' => 'Mie Ayam Suap',
            'slug' => 'mie-ayam-suap',
            'price' => 36000,
            'tag' => 'FAVORIT',
            'category_path' => 'MAKANAN / COMFORT FOOD',
            'subtitle' => 'Mie kenyal, ayam kecap',
            'description' => 'Mie kenyal resep khas dengan topping daging ayam cincang manis gurih, kuah kaldu hangat terpisah.',
            'composition' => 'Mie telur kenyal segar, tumis ayam cincang kecap manis gurih, caisim rebus, pangsit renyah, kuah kaldu ayam gurih.',
            'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Gurih manis', '1 porsi', '10-15 menit'],
            'allergens' => 'Mengandung gandum/gluten dan kedelai.',
            'is_signature' => true,
            'is_available' => true,
        ]);
        $optMie = ProductOption::create(['product_id' => $pMie->id, 'name' => 'Pilihan Mie', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optMie->id, 'name' => 'Mie keriting', 'extra_price' => 0, 'is_default' => true]);
        ProductChoice::create(['product_option_id' => $optMie->id, 'name' => 'Mie lebar', 'extra_price' => 0]);

        // 5.5 Nasi Gila Cikini
        Product::create([
            'category_id' => $catMakanan->id,
            'name' => 'Nasi Gila Cikini',
            'slug' => 'nasi-gila-cikini',
            'price' => 38000,
            'tag' => 'SPESIAL',
            'category_path' => 'MAKANAN / STREET FOOD',
            'subtitle' => 'Sosis, bakso, telur oseng pedas',
            'description' => 'Nasi hangat dengan tumisan melimpah sosis sapi, bakso sapi, dan telur berbalut bumbu gila pedas manis gurih.',
            'composition' => 'Nasi putih, sosis sapi, bakso sapi, telur ayam, sawi hijau, bumbu gila pedas manis gurih.',
            'image' => 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Pedas nagih', '1 porsi', '10-15 menit'],
            'allergens' => 'Mengandung telur, kedelai, dan olahan daging sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);

        // 5.6 Tahu Cabe Garam
        Product::create([
            'category_id' => $catCamilan->id,
            'name' => 'Tahu Cabe Garam',
            'slug' => 'tahu-cabe-garam',
            'price' => 20000,
            'tag' => 'CRISPY',
            'category_path' => 'CAMILAN / SHARING',
            'subtitle' => 'Tahu sutra crispy, cabe rawit',
            'description' => 'Tahu sutra dipotong dadu digoreng krispi dengan taburan bawang putih cincang gurih dan irisan cabai rawit segar.',
            'composition' => 'Tahu sutra organik, tepung krispi, bawang putih cincang goreng, cabai rawit merah segar, daun bawang, garam laut.',
            'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Gurih asin', '1 porsi', '5-10 menit'],
            'allergens' => 'Mengandung kedelai.',
            'is_signature' => false,
            'is_available' => true,
        ]);

        // 5.7 Kopi Susu Suap
        Product::create([
            'category_id' => $catMinuman->id,
            'name' => 'Kopi Susu Suap',
            'slug' => 'kopi-susu-suap',
            'price' => 25000,
            'tag' => 'FAVORIT',
            'category_path' => 'MINUMAN / KOPI',
            'subtitle' => 'Espresso, susu segar, aren asli',
            'description' => 'Double shot espresso blend arabika-robusta dengan susu pasteurisasi segar dan manis legit gula aren murni.',
            'composition' => 'Double shot espresso (Arabika Gayo & Robusta Dampit), susu segar pasteurisasi, gula aren murni organik cair, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Dingin / Hangat', 'Cup 12oz', '3 menit'],
            'allergens' => 'Mengandung produk susu sapi segar.',
            'is_signature' => false,
            'is_available' => true,
        ]);

        // 6. Demo Orders & Order Items
        OrderItem::truncate();
        Payment::truncate();
        Order::truncate();

        $table08 = $tables['08'] ?? Table::first();

        $demoOrder = Order::create([
            'order_number' => '#SP-0248',
            'table_id' => $table08->id,
            'table_number' => 'Meja 08',
            'customer_name' => 'Dina',
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'status' => 'cooking', // 4. Sedang dibuat
            'subtotal' => 88000,
            'service_fee' => 4400,
            'pbjt_tax' => 8800,
            'total_amount' => 101200,
            'gateway_reference' => 'QRIS-DEMO-SP-0248',
            'paid_at' => now()->subMinutes(12),
            'eta_range' => '12.50–12.55',
            'payment_time' => '12.31',
            'cooking_time' => '12.33',
        ]);

        OrderItem::create([
            'order_id' => $demoOrder->id,
            'product_id' => $pAyam->id,
            'product_name' => $pAyam->name,
            'price' => 42000,
            'quantity' => 1,
            'options_selected' => ['Nasi putih', 'Sambal dipisah'],
            'kitchen_note' => 'Ayamnya dipotong kecil, ya.',
            'subtotal' => 42000,
        ]);

        OrderItem::create([
            'order_id' => $demoOrder->id,
            'product_id' => $pJamur->id,
            'product_name' => $pJamur->name,
            'price' => 24000,
            'quantity' => 1,
            'options_selected' => ['Saus cocol terpisah'],
            'kitchen_note' => null,
            'subtotal' => 24000,
        ]);

        OrderItem::create([
            'order_id' => $demoOrder->id,
            'product_id' => $pTeh->id,
            'product_name' => $pTeh->name,
            'price' => 22000,
            'quantity' => 1,
            'options_selected' => ['Gula normal', 'Es normal'],
            'kitchen_note' => null,
            'subtotal' => 22000,
        ]);

        Payment::create([
            'order_id' => $demoOrder->id,
            'payment_code' => 'PAY-SP-0248',
            'gateway' => 'qris_dynamic',
            'amount' => 101200,
            'status' => 'success',
            'qris_payload' => '00020101021226600016ID.CO.QRIS.WWW01189360099900000000020215ID102002480000005204581253033605401012005802ID5911SUAP CIKINI6007JAKARTA61051033062160112SP0248MEJA086304',
            'expires_at' => now()->addMinutes(5),
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
