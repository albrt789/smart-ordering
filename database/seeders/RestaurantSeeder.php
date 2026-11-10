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
            'name' => 'Admin Fountain Hide',
            'email' => 'admin@fountainhide.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'phone' => '081288004499',
        ]);

        User::create([
            'name' => 'Barista & Kasir Fountain Hide',
            'email' => 'kasir@fountainhide.com',
            'password' => Hash::make('kasir123'),
            'role' => 'cashier',
            'is_active' => true,
            'phone' => '081288004488',
        ]);

        // Keep fallback accounts for compatibility
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

        // 2. Coffee Shop Settings (Exact branding from board: Fountain Hide Coffee, Jl. Tirta Bhakti No 23C)
        RestaurantSetting::truncate();
        RestaurantSetting::create([
            'name' => 'Fountain Hide',
            'branch' => 'Fountain Hide Coffee',
            'address' => 'Jl. Tirta Bhakti No. 23C',
            'phone' => '0812-8800-4499',
            'email' => 'hello@fountainhide.com',
            'open_hours' => '08:00 - 23:00 WIB',
            'service_fee_pct' => 5.00,
            'pbjt_tax_pct' => 10.00,
            'description' => 'Fountain Hide Coffee — Specialty coffee, artisanal handcrafted drinks, mocktails, and single origin manual brews. Nikmati kemudahan memesan mandiri langsung dari meja.',
            'logo' => '/images/logo.png',
        ]);

        // 3. Tables (Meja 01 s/d Meja 12)
        Table::truncate();
        $tables = [];
        for ($i = 1; $i <= 12; $i++) {
            $num = str_pad($i, 2, '0', STR_PAD_LEFT);
            $tableObj = Table::create([
                'table_number' => "Meja {$num}",
                'qr_token' => "fh-table-{$num}-" . Str::random(8),
                'is_active' => true,
            ]);
            $tables[$num] = $tableObj;
        }

        // 4. Categories (Exact 5 categories from the menu board)
        Category::truncate();
        $catSignatures   = Category::create(['name' => 'Signatures', 'slug' => 'signatures', 'sort_order' => 1]);
        $catCoffeeBased  = Category::create(['name' => 'Coffee Based', 'slug' => 'coffee-based', 'sort_order' => 2]);
        $catTeaNonCoffee = Category::create(['name' => 'Tea & Non-Coffee', 'slug' => 'tea-non-coffee', 'sort_order' => 3]);
        $catMocktails    = Category::create(['name' => 'Mocktails', 'slug' => 'mocktails', 'sort_order' => 4]);
        $catManualBrew   = Category::create(['name' => 'Manual Brew', 'slug' => 'manual-brew', 'sort_order' => 5]);

        // 5. Products & Modifiers
        ProductChoice::truncate();
        ProductOption::truncate();
        Product::truncate();

        // Helper closures for common options
        $addIceOption = function ($product) {
            $opt = ProductOption::create(['product_id' => $product->id, 'name' => 'Pilihan Es', 'is_required' => true]);
            ProductChoice::create(['product_option_id' => $opt->id, 'name' => 'Normal Ice', 'extra_price' => 0, 'is_default' => true]);
            ProductChoice::create(['product_option_id' => $opt->id, 'name' => 'Less Ice', 'extra_price' => 0]);
        };

        $addSweetOption = function ($product) {
            $opt = ProductOption::create(['product_id' => $product->id, 'name' => 'Tingkat Manis', 'is_required' => true]);
            ProductChoice::create(['product_option_id' => $opt->id, 'name' => 'Normal Sweet (100%)', 'extra_price' => 0, 'is_default' => true]);
            ProductChoice::create(['product_option_id' => $opt->id, 'name' => 'Less Sweet (50%)', 'extra_price' => 0]);
            ProductChoice::create(['product_option_id' => $opt->id, 'name' => 'Tanpa Gula', 'extra_price' => 0]);
        };

        $addTempOption = function ($product, $default = 'Dingin (Iced)') {
            $opt = ProductOption::create(['product_id' => $product->id, 'name' => 'Suhu Penyajian', 'is_required' => true]);
            ProductChoice::create(['product_option_id' => $opt->id, 'name' => 'Dingin (Iced)', 'extra_price' => 0, 'is_default' => $default === 'Dingin (Iced)']);
            ProductChoice::create(['product_option_id' => $opt->id, 'name' => 'Hangat (Hot)', 'extra_price' => 0, 'is_default' => $default === 'Hangat (Hot)']);
        };

        $addMilkOption = function ($product) {
            $opt = ProductOption::create(['product_id' => $product->id, 'name' => 'Pilihan Susu', 'is_required' => true]);
            ProductChoice::create(['product_option_id' => $opt->id, 'name' => 'Fresh Milk (Susu Sapi)', 'extra_price' => 0, 'is_default' => true]);
            ProductChoice::create(['product_option_id' => $opt->id, 'name' => 'Oatly Oat Milk (+6K)', 'extra_price' => 6000]);
            ProductChoice::create(['product_option_id' => $opt->id, 'name' => 'Almond Milk (+6K)', 'extra_price' => 6000]);
        };

        // ==========================================
        // SECTION 1: SIGNATURES
        // ==========================================

        // 1.1 Number 1 (23K)
        $pNumber1 = Product::create([
            'category_id' => $catSignatures->id,
            'name' => 'Number 1',
            'slug' => 'number-1',
            'price' => 23000,
            'stock' => 80,
            'daily_stock' => 80,
            'track_stock' => true,
            'tag' => 'SIGNATURE',
            'category_path' => 'SIGNATURES / HOUSE SPECIAL',
            'subtitle' => 'Signature Coffee #1 Fountain Hide',
            'description' => 'Signature iced coffee nomor 1 Fountain Hide. Paduan double espresso mantap, creamy susu lembut, dan sentuhan racikan manis rahasia yang menjadi favorit utama pelanggan.',
            'composition' => 'Double shot specialty espresso blend, fresh milk pasteurisasi, signature caramelize syrup, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Signature #1', 'Best Seller', '3-5 menit'],
            'allergens' => 'Mengandung susu sapi segar. Tersedia opsi susu oat / almond.',
            'is_signature' => true,
            'is_available' => true,
        ]);
        $addIceOption($pNumber1);
        $addSweetOption($pNumber1);
        $addMilkOption($pNumber1);

        // 1.2 De Javu (25K)
        $pDeJavu = Product::create([
            'category_id' => $catSignatures->id,
            'name' => 'De Javu',
            'slug' => 'de-javu',
            'price' => 25000,
            'stock' => 60,
            'daily_stock' => 60,
            'track_stock' => true,
            'tag' => 'SIGNATURE',
            'category_path' => 'SIGNATURES / SPECIALTY',
            'subtitle' => 'Creamy & Nostalgic Espresso Blend',
            'description' => 'Kreasi signature espresso lembut dengan paduan house secret blend Fountain Hide yang menghadirkan rasa nostalgia yang tak terlupakan.',
            'composition' => 'Espresso blend, fresh milk, brown sugar reduction, vanilla hint, cold froth cream.',
            'image' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Nostalgic', 'Creamy', 'Dingin'],
            'allergens' => 'Mengandung susu sapi segar.',
            'is_signature' => true,
            'is_available' => true,
        ]);
        $addIceOption($pDeJavu);
        $addSweetOption($pDeJavu);
        $addMilkOption($pDeJavu);

        // 1.3 Fcoff (25K)
        $pFcoff = Product::create([
            'category_id' => $catSignatures->id,
            'name' => 'Fcoff',
            'slug' => 'fcoff',
            'price' => 25000,
            'stock' => 70,
            'daily_stock' => 70,
            'track_stock' => true,
            'tag' => 'SIGNATURE',
            'category_path' => 'SIGNATURES / SPECIALTY',
            'subtitle' => 'Fountain Hide Original Blend',
            'description' => 'Fountain Hide original iced coffee. Kopi susu otentik dengan keseimbangan rasa espresso bold dan creamy susu berkualitas tinggi.',
            'composition' => 'Double shot espresso, fresh milk, organic palm nectar, ice.',
            'image' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Original Recipe', 'Smooth', 'Dingin'],
            'allergens' => 'Mengandung susu sapi segar.',
            'is_signature' => true,
            'is_available' => true,
        ]);
        $addIceOption($pFcoff);
        $addSweetOption($pFcoff);

        // 1.4 Fat Americano (25K)
        $pFatAme = Product::create([
            'category_id' => $catSignatures->id,
            'name' => 'Fat Americano',
            'slug' => 'fat-americano',
            'price' => 25000,
            'stock' => 50,
            'daily_stock' => 50,
            'track_stock' => true,
            'tag' => 'SIGNATURE',
            'category_path' => 'SIGNATURES / BLACK SPECIALTY',
            'subtitle' => 'Bold Espresso with Citrus Splash',
            'description' => 'Double shot espresso pekat bervolume ekstra dengan kesegaran splash citrus alami dan sparkling tonic dingin yang menyegarkan dahaga.',
            'composition' => 'Triple-pull ristretto espresso, sparkling tonic water, fresh lime/lemon slice, ice.',
            'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Bold & Fresh', '0 Sugar Option', 'Dingin'],
            'allergens' => 'Bebas susu, bebas gluten, vegan-friendly.',
            'is_signature' => true,
            'is_available' => true,
        ]);
        $addIceOption($pFatAme);
        $addSweetOption($pFatAme);

        // 1.5 Pinky Pi (26K)
        $pPinkyPi = Product::create([
            'category_id' => $catSignatures->id,
            'name' => 'Pinky Pi',
            'slug' => 'pinky-pi',
            'price' => 26000,
            'stock' => 45,
            'daily_stock' => 45,
            'track_stock' => true,
            'tag' => 'SIGNATURE',
            'category_path' => 'SIGNATURES / CREATIVE',
            'subtitle' => 'Sweet Berry Cream Signature',
            'description' => 'Kreasi signature bernuansa pink ceria dengan paduan rasa berry segar, aroma floral manis, dan susu lembut yang creamy memikat.',
            'composition' => 'Puree stroberi artisan, milk cloud, hint of rose tea extract, crushed strawberry garnish.',
            'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Fruity Sweet', 'Instagrammable', 'Dingin'],
            'allergens' => 'Mengandung produk olahan susu segar.',
            'is_signature' => true,
            'is_available' => true,
        ]);
        $addIceOption($pPinkyPi);
        $addSweetOption($pPinkyPi);

        // 1.6 Mont Blanc (29K)
        $pMontBlanc = Product::create([
            'category_id' => $catSignatures->id,
            'name' => 'Mont Blanc',
            'slug' => 'mont-blanc',
            'price' => 29000,
            'stock' => 40,
            'daily_stock' => 40,
            'track_stock' => true,
            'tag' => 'PREMIUM SIGNATURE',
            'category_path' => 'SIGNATURES / LUXE',
            'subtitle' => 'Espresso with Luxurious Cold Cream Cloud',
            'description' => 'Espresso signature terinspirasi puncak salju Mont Blanc dengan topping vanilla cold cream tebal, creamy, dan mewah bertabur cocoa dust.',
            'composition' => 'Double shot espresso, fresh milk base, thick vanilla sea salt cold foam, Belgian cocoa dust.',
            'image' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Thick Cream', 'Premium', 'Dingin'],
            'allergens' => 'Mengandung susu sapi segar.',
            'is_signature' => true,
            'is_available' => true,
        ]);
        $addIceOption($pMontBlanc);
        $addSweetOption($pMontBlanc);

        // 1.7 Mineral Water (10K)
        $pMineral = Product::create([
            'category_id' => $catSignatures->id,
            'name' => 'Mineral Water',
            'slug' => 'mineral-water',
            'price' => 10000,
            'stock' => 100,
            'daily_stock' => 100,
            'track_stock' => true,
            'tag' => 'ESSENTIAL',
            'category_path' => 'SIGNATURES / ESSENTIALS',
            'subtitle' => 'Air Mineral Pegunungan Murni',
            'description' => 'Air mineral botol pegunungan murni berkualitas tinggi untuk menyegarkan tenggorokan.',
            'composition' => 'Air mineral alami 100%.',
            'image' => 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Air Murni', 'Botol 600ml'],
            'allergens' => 'Bebas alergen.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $optMinTemp = ProductOption::create(['product_id' => $pMineral->id, 'name' => 'Suhu Penyajian', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optMinTemp->id, 'name' => 'Dingin (Chilled)', 'extra_price' => 0, 'is_default' => true]);
        ProductChoice::create(['product_option_id' => $optMinTemp->id, 'name' => 'Suhu Ruang (Normal)', 'extra_price' => 0]);

        // 1.8 Extra Espresso Shot (5K)
        $pExtraShot = Product::create([
            'category_id' => $catSignatures->id,
            'name' => 'Extra Espresso Shot',
            'slug' => 'extra-espresso-shot',
            'price' => 5000,
            'stock' => 999,
            'daily_stock' => 999,
            'track_stock' => false,
            'tag' => 'ADD-ON',
            'category_path' => 'SIGNATURES / ADD-ON',
            'subtitle' => 'Single Specialty Espresso Shot',
            'description' => 'Tambahan single shot espresso specialty roastery untuk rasa kopi yang lebih kuat, pekat, dan berenergi.',
            'composition' => 'Single shot specialty Arabika espresso freshly extracted.',
            'image' => 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Specialty Espresso', 'Add-on'],
            'allergens' => 'Bebas alergen.',
            'is_signature' => false,
            'is_available' => true,
        ]);

        // ==========================================
        // SECTION 2: COFFEE BASED
        // ==========================================

        // 2.1 Nobb (20K)
        $pNobb = Product::create([
            'category_id' => $catCoffeeBased->id,
            'name' => 'Nobb',
            'slug' => 'nobb',
            'price' => 20000,
            'stock' => 80,
            'daily_stock' => 80,
            'track_stock' => true,
            'tag' => 'DAILY COFFEE',
            'category_path' => 'COFFEE BASED / DAILY',
            'subtitle' => 'Kopi Susu Harian Ramah Kantong',
            'description' => 'Kopi susu harian Fountain Hide dengan racikan kopi nikmat, creamy susu lembut, dan harga bersahabat menemani santai dan kerja.',
            'composition' => 'Espresso house blend, susu segar, condensed milk, gula tebu alami.',
            'image' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Hemat 20K', 'Daily Coffee', 'Dingin'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pNobb);
        $addSweetOption($pNobb);

        // 2.2 Black (25K)
        $pBlack = Product::create([
            'category_id' => $catCoffeeBased->id,
            'name' => 'Black',
            'slug' => 'black',
            'price' => 25000,
            'stock' => 70,
            'daily_stock' => 70,
            'track_stock' => true,
            'tag' => 'CLASSIC BLACK',
            'category_path' => 'COFFEE BASED / BLACK',
            'subtitle' => 'Specialty Long Black / Americano',
            'description' => 'Ekstraksi espresso murni dipadukan dengan air mineral bersuhu tepat. Menampilkan notes floral, cokelat, dan aroma kopi yang bersih.',
            'composition' => 'Double shot espresso specialty Arabika, air mineral.',
            'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Pure Coffee', '0 Kalori', 'Dingin / Hangat'],
            'allergens' => 'Bebas alergen, vegan.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addTempOption($pBlack);
        $optBlackSugar = ProductOption::create(['product_id' => $pBlack->id, 'name' => 'Pilihan Gula', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optBlackSugar->id, 'name' => 'Tanpa Gula (0 Kalori)', 'extra_price' => 0, 'is_default' => true]);
        ProductChoice::create(['product_option_id' => $optBlackSugar->id, 'name' => 'Normal Sweet', 'extra_price' => 0]);
        ProductChoice::create(['product_option_id' => $optBlackSugar->id, 'name' => 'Less Sweet', 'extra_price' => 0]);

        // 2.3 White (25K)
        $pWhite = Product::create([
            'category_id' => $catCoffeeBased->id,
            'name' => 'White',
            'slug' => 'white',
            'price' => 25000,
            'stock' => 70,
            'daily_stock' => 70,
            'track_stock' => true,
            'tag' => 'CLASSIC WHITE',
            'category_path' => 'COFFEE BASED / WHITE',
            'subtitle' => 'Specialty Cafe Latte / Flat White',
            'description' => 'Perpaduan klasik espresso specialty dan steamed fresh milk dengan microfoam lembut yang creamy dan seimbang.',
            'composition' => 'Double shot espresso Arabika, fresh milk pasteurisasi.',
            'image' => 'https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Smooth Latte', 'Dingin / Hangat'],
            'allergens' => 'Mengandung susu sapi. Tersedia opsi susu oat / almond.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addTempOption($pWhite);
        $addMilkOption($pWhite);

        // 2.4 Vanilla Latte (25K)
        $pVanillaLatte = Product::create([
            'category_id' => $catCoffeeBased->id,
            'name' => 'Vanilla Latte',
            'slug' => 'vanilla-latte',
            'price' => 25000,
            'stock' => 60,
            'daily_stock' => 60,
            'track_stock' => true,
            'tag' => 'FLAVOURED LATTE',
            'category_path' => 'COFFEE BASED / FLAVOURED',
            'subtitle' => 'Espresso & Fresh Milk with French Vanilla',
            'description' => 'Espresso dan fresh milk berpadu dengan sirup vanilla aromatik yang manis lembut dan harum menenangkan.',
            'composition' => 'Espresso, susu segar, sirup ekstrak vanila murni, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1570968915860-54d5c301fa9f?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Aromatic', 'Dingin / Hangat'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addTempOption($pVanillaLatte);
        $addSweetOption($pVanillaLatte);

        // 2.5 Strawberry Latte (25K)
        $pStrawberryLatte = Product::create([
            'category_id' => $catCoffeeBased->id,
            'name' => 'Strawberry Latte',
            'slug' => 'strawberry-latte',
            'price' => 25000,
            'stock' => 50,
            'daily_stock' => 50,
            'track_stock' => true,
            'tag' => 'FLAVOURED LATTE',
            'category_path' => 'COFFEE BASED / FLAVOURED',
            'subtitle' => 'Artisan Strawberry Puree with Latte',
            'description' => 'Sensasi buah stroberi segar yang asam manis berpadu apik dengan lembutnya susu dan single shot espresso specialty.',
            'composition' => 'Espresso shot, puree buah stroberi asli, susu segar, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1553787499-6f9133860278?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Fruity Coffee', 'Dingin'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pStrawberryLatte);
        $addSweetOption($pStrawberryLatte);

        // 2.6 Butterscotch Latte (27K)
        $pButterscotchLatte = Product::create([
            'category_id' => $catCoffeeBased->id,
            'name' => 'Butterscotch Latte',
            'slug' => 'butterscotch-latte',
            'price' => 27000,
            'stock' => 55,
            'daily_stock' => 55,
            'track_stock' => true,
            'tag' => 'POPULER',
            'category_path' => 'COFFEE BASED / FLAVOURED',
            'subtitle' => 'Smoked Butterscotch & Caramelized Cream',
            'description' => 'Espresso susu dengan saus butterscotch karamel panggang manis gurih harum yang meleleh nikmat di lidah.',
            'composition' => 'Double shot espresso, fresh milk, saus butterscotch karamel mentega, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1577968897966-3d4325b36b61?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Caramel Buttery', 'Best Seller', 'Dingin'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pButterscotchLatte);
        $addSweetOption($pButterscotchLatte);

        // 2.7 Seasalt Latte (27K)
        $pSeasaltLatte = Product::create([
            'category_id' => $catCoffeeBased->id,
            'name' => 'Seasalt Latte',
            'slug' => 'seasalt-latte',
            'price' => 27000,
            'stock' => 50,
            'daily_stock' => 50,
            'track_stock' => true,
            'tag' => 'POPULER',
            'category_path' => 'COFFEE BASED / FLAVOURED',
            'subtitle' => 'Savory Seasalt Cream Foam on Latte',
            'description' => 'Kopi susu dengan lapisan krim sea salt gurih asin yang mengambang lembut di atas espresso latte.',
            'composition' => 'Espresso, susu segar, seasalt whipping cream foam, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Savory Sweet', 'Favorit', 'Dingin'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pSeasaltLatte);
        $addSweetOption($pSeasaltLatte);

        // 2.8 Mocha Latte (27K)
        $pMochaLatte = Product::create([
            'category_id' => $catCoffeeBased->id,
            'name' => 'Mocha Latte',
            'slug' => 'mocha-latte',
            'price' => 27000,
            'stock' => 45,
            'daily_stock' => 45,
            'track_stock' => true,
            'tag' => 'COFFEE BASED',
            'category_path' => 'COFFEE BASED / CHOCOLATE',
            'subtitle' => 'Dark Belgian Chocolate & Specialty Espresso',
            'description' => 'Harmoni cokelat hitam pekat pilihan dengan double shot espresso dan fresh milk yang kaya rasa cokelat kopi premium.',
            'composition' => 'Double shot espresso, pasta dark chocolate premium, steamed/chilled fresh milk.',
            'image' => 'https://images.unsplash.com/photo-1578314675249-a6910f80cc4e?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Choco Coffee', 'Dingin / Hangat'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addTempOption($pMochaLatte);
        $addSweetOption($pMochaLatte);

        // 2.9 Caramel Machiato (28K)
        $pCaramelMachiato = Product::create([
            'category_id' => $catCoffeeBased->id,
            'name' => 'Caramel Machiato',
            'slug' => 'caramel-machiato',
            'price' => 28000,
            'stock' => 50,
            'daily_stock' => 50,
            'track_stock' => true,
            'tag' => 'FAVORIT',
            'category_path' => 'COFFEE BASED / SPECIALTY',
            'subtitle' => 'Layered Vanilla Milk, Espresso & Caramel Drizzle',
            'description' => 'Susu vanila lembut berlayer espresso pekat dengan siraman saus karamel lezat di atas foam yang memanjakan lidah.',
            'composition' => 'Espresso shot, steamed/iced fresh milk, sirup vanilla, caramel drizzle sauce.',
            'image' => 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Caramel Drizzle', 'Favorit', 'Dingin'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pCaramelMachiato);
        $addSweetOption($pCaramelMachiato);

        // 2.10 Butterscotch Sea Salt Latte (29K)
        $pButterscotchSeaSalt = Product::create([
            'category_id' => $catCoffeeBased->id,
            'name' => 'Butterscotch Sea Salt Latte',
            'slug' => 'butterscotch-sea-salt-latte',
            'price' => 29000,
            'stock' => 60,
            'daily_stock' => 60,
            'track_stock' => true,
            'tag' => 'TOP SIGNATURE',
            'category_path' => 'COFFEE BASED / SIGNATURE',
            'subtitle' => 'The Ultimate Sweet & Savory Coffee Experience',
            'description' => 'Mahakarya perpaduan butterscotch harum legit, seasalt foam gurih asin, dan double shot espresso specialty roastery.',
            'composition' => 'Double shot espresso, fresh milk, sirup smoked butterscotch, foam krim sea salt, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Must Try', 'Top Pick', 'Dingin'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => true,
            'is_available' => true,
        ]);
        $addIceOption($pButterscotchSeaSalt);
        $addSweetOption($pButterscotchSeaSalt);

        // ==========================================
        // SECTION 3: TEA & NON-COFFEE
        // ==========================================

        // 3.1 Black Tea (20K)
        $pBlackTea = Product::create([
            'category_id' => $catTeaNonCoffee->id,
            'name' => 'Black Tea',
            'slug' => 'black-tea',
            'price' => 20000,
            'stock' => 70,
            'daily_stock' => 70,
            'track_stock' => true,
            'tag' => 'TEA',
            'category_path' => 'TEA & NON-COFFEE / TEA',
            'subtitle' => 'Pure Brewed Black Tea',
            'description' => 'Seduhan daun teh hitam berkualitas dengan aroma wangi khas dan rasa menenangkan yang kaya antioksidan.',
            'composition' => 'Seduhan daun teh hitam pilihan, air murni.',
            'image' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Classic Tea', 'Dingin / Hangat'],
            'allergens' => 'Bebas alergen utama.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addTempOption($pBlackTea);
        $addSweetOption($pBlackTea);

        // 3.2 Peach Tea (22K)
        $pPeachTea = Product::create([
            'category_id' => $catTeaNonCoffee->id,
            'name' => 'Peach Tea',
            'slug' => 'peach-tea',
            'price' => 22000,
            'stock' => 60,
            'daily_stock' => 60,
            'track_stock' => true,
            'tag' => 'TEA FAVORIT',
            'category_path' => 'TEA & NON-COFFEE / FRUIT TEA',
            'subtitle' => 'Aromatic Sweet Peach Iced Tea',
            'description' => 'Teh hitam harum berpadu sari buah persik (peach) manis segar yang menyejukkan dahaga seketika.',
            'composition' => 'Seduhan teh premium, puree dan ekstrak buah peach, irisan buah peach, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Fruity Fresh', 'Dingin'],
            'allergens' => 'Bebas alergen utama.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pPeachTea);
        $addSweetOption($pPeachTea);

        // 3.3 Lemon Tea (22K)
        $pLemonTea = Product::create([
            'category_id' => $catTeaNonCoffee->id,
            'name' => 'Lemon Tea',
            'slug' => 'lemon-tea',
            'price' => 22000,
            'stock' => 60,
            'daily_stock' => 60,
            'track_stock' => true,
            'tag' => 'TEA FRESH',
            'category_path' => 'TEA & NON-COFFEE / FRUIT TEA',
            'subtitle' => 'Fresh Brewed Tea with Pure Lemon Juice',
            'description' => 'Seduhan teh segar berpadu perasan sari lemon asli yang kaya vitamin C dengan rasa asam manis segar alami.',
            'composition' => 'Seduhan teh hitam, perasan sari lemon segar, gula tebu alami, irisan lemon, es batu.',
            'image' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Real Lemon', 'Segar', 'Dingin'],
            'allergens' => 'Bebas alergen utama.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pLemonTea);
        $addSweetOption($pLemonTea);

        // 3.4 Choco Latte (25K)
        $pChocoLatte = Product::create([
            'category_id' => $catTeaNonCoffee->id,
            'name' => 'Choco Latte',
            'slug' => 'choco-latte',
            'price' => 25000,
            'stock' => 50,
            'daily_stock' => 50,
            'track_stock' => true,
            'tag' => 'NON-COFFEE',
            'category_path' => 'TEA & NON-COFFEE / CHOCOLATE',
            'subtitle' => 'Velvety Belgian Cocoa with Fresh Milk',
            'description' => 'Cokelat pekat lezat dipadukan dengan susu segar hangat atau dingin yang kaya rasa cokelat asli berkualitas.',
            'composition' => 'Bubuk dark cocoa murni, susu segar, pemanis alami tebu.',
            'image' => 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Rich Cocoa', 'Dingin / Hangat'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addTempOption($pChocoLatte);
        $addSweetOption($pChocoLatte);

        // 3.5 Matcha Latte (26K)
        $pMatchaLatte = Product::create([
            'category_id' => $catTeaNonCoffee->id,
            'name' => 'Matcha Latte',
            'slug' => 'matcha-latte',
            'price' => 26000,
            'stock' => 50,
            'daily_stock' => 50,
            'track_stock' => true,
            'tag' => 'NON-COFFEE',
            'category_path' => 'TEA & NON-COFFEE / MATCHA',
            'subtitle' => 'Pure Japanese Green Tea Matcha with Milk',
            'description' => 'Bubuk matcha Jepang murni di-whisk sempurna bersama fresh milk lembut menghasilkan aroma rumput laut manis yang otentik.',
            'composition' => 'Bubuk matcha Jepang murni, fresh milk pasteurisasi, simple syrup alami.',
            'image' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Pure Matcha', 'Dingin / Hangat'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addTempOption($pMatchaLatte);
        $addSweetOption($pMatchaLatte);

        // 3.6 Biscoff Latte (26K)
        $pBiscoffLatte = Product::create([
            'category_id' => $catTeaNonCoffee->id,
            'name' => 'Biscoff Latte',
            'slug' => 'biscoff-latte',
            'price' => 26000,
            'stock' => 45,
            'daily_stock' => 45,
            'track_stock' => true,
            'tag' => 'NON-COFFEE',
            'category_path' => 'TEA & NON-COFFEE / BISCOFF',
            'subtitle' => 'Lotus Biscoff Spread & Crunchy Biscuit Crumbs',
            'description' => 'Susu segar lembut berpadu lelehan selai karamel biskuit Lotus Biscoff dan taburan remah biskuit renyah.',
            'composition' => 'Selai Lotus Biscoff murni, fresh milk, biskuit Biscoff crumble, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Biscoff Crumbs', 'Manis Gurih', 'Dingin'],
            'allergens' => 'Mengandung susu sapi dan gluten biskuit.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pBiscoffLatte);
        $addSweetOption($pBiscoffLatte);

        // 3.7 Minty Matcha (28K)
        $pMintyMatcha = Product::create([
            'category_id' => $catTeaNonCoffee->id,
            'name' => 'Minty Matcha',
            'slug' => 'minty-matcha',
            'price' => 28000,
            'stock' => 40,
            'daily_stock' => 40,
            'track_stock' => true,
            'tag' => 'SPECIAL BLEND',
            'category_path' => 'TEA & NON-COFFEE / MATCHA',
            'subtitle' => 'Cooling Mint Infused Japanese Matcha Latte',
            'description' => 'Sensasi dingin semriwing daun mint berpadu elegan dengan pekatnya matcha Jepang dan susu creamy menenangkan.',
            'composition' => 'Matcha Jepang, sirup mint segar, susu segar, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1515823662972-da6a2e4d3002?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Minty Fresh', 'Unik', 'Dingin'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pMintyMatcha);
        $addSweetOption($pMintyMatcha);

        // 3.8 Minty Choco (28K)
        $pMintyChoco = Product::create([
            'category_id' => $catTeaNonCoffee->id,
            'name' => 'Minty Choco',
            'slug' => 'minty-choco',
            'price' => 28000,
            'stock' => 40,
            'daily_stock' => 40,
            'track_stock' => true,
            'tag' => 'SPECIAL BLEND',
            'category_path' => 'TEA & NON-COFFEE / CHOCOLATE',
            'subtitle' => 'European Style Refreshing Choco Mint Latte',
            'description' => 'Paduan mewah cokelat pekat dan sirup mint segar dingin ala cita rasa cokelat mint klasik eropa yang nagih.',
            'composition' => 'Dark cocoa premium, sirup mint alami, susu segar, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Choco Mint', 'Favorit', 'Dingin'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pMintyChoco);
        $addSweetOption($pMintyChoco);

        // 3.9 Choco Vanilla (28K)
        $pChocoVanilla = Product::create([
            'category_id' => $catTeaNonCoffee->id,
            'name' => 'Choco Vanilla',
            'slug' => 'choco-vanilla',
            'price' => 28000,
            'stock' => 45,
            'daily_stock' => 45,
            'track_stock' => true,
            'tag' => 'SPECIAL BLEND',
            'category_path' => 'TEA & NON-COFFEE / CHOCOLATE',
            'subtitle' => 'Rich Cocoa & Bourbon Vanilla Fusion',
            'description' => 'Harmoni cokelat premium dengan sentuhan manis lembut ekstrak vanila dan susu segar berlimpah rasa.',
            'composition' => 'Kakao hitam pilihan, ekstrak vanilla murni, susu pasteurisasi segar.',
            'image' => 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Creamy Vanilla', 'Dingin / Hangat'],
            'allergens' => 'Mengandung susu sapi.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addTempOption($pChocoVanilla);
        $addSweetOption($pChocoVanilla);

        // ==========================================
        // SECTION 4: MOCKTAILS
        // ==========================================

        // 4.1 Valonia (25K)
        $pValonia = Product::create([
            'category_id' => $catMocktails->id,
            'name' => 'Valonia',
            'slug' => 'valonia',
            'price' => 25000,
            'stock' => 50,
            'daily_stock' => 50,
            'track_stock' => true,
            'tag' => 'MOCKTAIL',
            'category_path' => 'MOCKTAILS / SPARKLING',
            'subtitle' => 'Sparkling Tropical Berry & Citrus Mocktail',
            'description' => 'Mocktail signature dengan cita rasa buah segar tropis, soda berdesis ria, dan aroma floral yang membangkitkan kesegaran.',
            'composition' => 'Sari buah berry tropis, sparkling soda water, lime wedge, mint garnish, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Sparkling', 'Fruity', 'Dingin'],
            'allergens' => 'Bebas alergen, vegan-friendly.',
            'is_signature' => true,
            'is_available' => true,
        ]);
        $addIceOption($pValonia);

        // 4.2 Sorry Pea (25K)
        $pSorryPea = Product::create([
            'category_id' => $catMocktails->id,
            'name' => 'Sorry Pea',
            'slug' => 'sorry-pea',
            'price' => 25000,
            'stock' => 45,
            'daily_stock' => 45,
            'track_stock' => true,
            'tag' => 'MOCKTAIL',
            'category_path' => 'MOCKTAILS / FLORAL',
            'subtitle' => 'Butterfly Pea Flower Citrus Gradient Mocktail',
            'description' => 'Mocktail eksotis berbasis ekstrak bunga telang alami dengan gradasi warna ungu biru cantik dan rasa manis asam menyegarkan.',
            'composition' => 'Ekstrak bunga telang organik, perasan jeruk nipis segar, simple syrup, soda water dingin.',
            'image' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Butterfly Pea', 'Visual Cantik', 'Dingin'],
            'allergens' => 'Bebas alergen, vegan-friendly.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pSorryPea);

        // 4.3 Alexis (25K)
        $pAlexis = Product::create([
            'category_id' => $catMocktails->id,
            'name' => 'Alexis',
            'slug' => 'alexis',
            'price' => 25000,
            'stock' => 50,
            'daily_stock' => 50,
            'track_stock' => true,
            'tag' => 'MOCKTAIL',
            'category_path' => 'MOCKTAILS / CITRUS',
            'subtitle' => 'Ruby Red Berry & Lime Zest Refresher',
            'description' => 'Kombinasi menyegarkan sari buah merah delima & kranberi, kesegaran jeruk nipis, dan soda dingin yang membangkitkan mood seketika.',
            'composition' => 'Cranberry & pomegranate infusion, fresh lime juice, sparkling water, fresh mint.',
            'image' => 'https://images.unsplash.com/photo-1546171753-97d7676e4602?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Citrus Berry', 'Energy Booster', 'Dingin'],
            'allergens' => 'Bebas alergen, vegan-friendly.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pAlexis);

        // 4.4 Bella Verde (25K)
        $pBellaVerde = Product::create([
            'category_id' => $catMocktails->id,
            'name' => 'Bella Verde',
            'slug' => 'bella-verde',
            'price' => 25000,
            'stock' => 50,
            'daily_stock' => 50,
            'track_stock' => true,
            'tag' => 'MOCKTAIL',
            'category_path' => 'MOCKTAILS / HERBAL',
            'subtitle' => 'Crisp Green Apple, Garden Mint & Tonic',
            'description' => 'Mocktail hijau menyejukkan dengan paduan sari apel hijau renyah, daun mint segar, dan sentuhan tonic berkarbonasi.',
            'composition' => 'Green apple nectar, crushed mint leaves, lime juice, premium tonic water, es kristal.',
            'image' => 'https://images.unsplash.com/photo-1536935338788-846bb9981813?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Green Apple & Mint', 'Sangat Segar', 'Dingin'],
            'allergens' => 'Bebas alergen, vegan-friendly.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $addIceOption($pBellaVerde);

        // ==========================================
        // SECTION 5: MANUAL BREW
        // ==========================================

        // 5.1 Manual Brew (25K)
        $pManualBrew = Product::create([
            'category_id' => $catManualBrew->id,
            'name' => 'Manual Brew',
            'slug' => 'manual-brew',
            'price' => 25000,
            'stock' => 60,
            'daily_stock' => 60,
            'track_stock' => true,
            'tag' => 'SLOW BAR',
            'category_path' => 'MANUAL BREW / FILTER',
            'subtitle' => 'V60 Pour Over Single Origin Specialty',
            'description' => 'Seduhan manual filter V60 dengan biji kopi single origin pilihan roastery Fountain Hide. Menghasilkan cup yang bersih dengan aroma floral dan rasa buah yang jernih.',
            'composition' => 'Single origin specialty beans (pilihan: Gayo / Kerinci / Flores), air mineral suhu 92°C diseduh perlahan.',
            'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=80',
            'badges' => ['V60 Hand Drip', 'Single Origin', '3-5 menit'],
            'allergens' => 'Bebas alergen, 0 gula, vegan.',
            'is_signature' => false,
            'is_available' => true,
        ]);
        $optMethodMB = ProductOption::create(['product_id' => $pManualBrew->id, 'name' => 'Metode Seduh', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optMethodMB->id, 'name' => 'V60 Pour Over (Hot)', 'extra_price' => 0, 'is_default' => true]);
        ProductChoice::create(['product_option_id' => $optMethodMB->id, 'name' => 'Japanese Iced Drip (Cold)', 'extra_price' => 0]);

        $optBeansMB = ProductOption::create(['product_id' => $pManualBrew->id, 'name' => 'Profil Rasa Beans', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optBeansMB->id, 'name' => 'Fruity & Bright Acidity', 'extra_price' => 0, 'is_default' => true]);
        ProductChoice::create(['product_option_id' => $optBeansMB->id, 'name' => 'Nutty & Chocolate Body', 'extra_price' => 0]);
        ProductChoice::create(['product_option_id' => $optBeansMB->id, 'name' => 'Floral & Sweet Finish', 'extra_price' => 0]);

        // 5.2 Manual Brew S (29K)
        $pManualBrewS = Product::create([
            'category_id' => $catManualBrew->id,
            'name' => 'Manual Brew S',
            'slug' => 'manual-brew-s',
            'price' => 29000,
            'stock' => 40,
            'daily_stock' => 40,
            'track_stock' => true,
            'tag' => 'SPECIAL RESERVE',
            'category_path' => 'MANUAL BREW / RESERVE',
            'subtitle' => 'Special Reserve / Rare Anaerobic Micro-lot Beans',
            'description' => 'Seduhan manual kasta Special Reserve menggunakan biji kopi langka / varietas Gesha / proses Anaerobic eksperimental kurasi khusus roaster Fountain Hide.',
            'composition' => 'Special Reserve Micro-lot beans (varietas langka / proses fermentasi buah eksotis).',
            'image' => 'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=800&q=80',
            'badges' => ['Special Reserve', 'Rare Process', 'Complex Notes'],
            'allergens' => 'Bebas alergen, 0 gula, vegan.',
            'is_signature' => true,
            'is_available' => true,
        ]);
        $optMethodMBS = ProductOption::create(['product_id' => $pManualBrewS->id, 'name' => 'Metode Seduh', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optMethodMBS->id, 'name' => 'V60 Pour Over (Hot)', 'extra_price' => 0, 'is_default' => true]);
        ProductChoice::create(['product_option_id' => $optMethodMBS->id, 'name' => 'Japanese Iced Drip (Cold)', 'extra_price' => 0]);

        $optBeansMBS = ProductOption::create(['product_id' => $pManualBrewS->id, 'name' => 'Pilihan Beans Reserve', 'is_required' => true]);
        ProductChoice::create(['product_option_id' => $optBeansMBS->id, 'name' => 'Anaerobic Natural Sweet', 'extra_price' => 0, 'is_default' => true]);
        ProductChoice::create(['product_option_id' => $optBeansMBS->id, 'name' => 'Extended Fermentation Gesha Style', 'extra_price' => 0]);

        // ==========================================
        // 6. Demo Orders & Order Items
        // ==========================================
        OrderItem::truncate();
        Payment::truncate();
        Order::truncate();

        $table08 = $tables['08'] ?? Table::first();

        $subtotal = $pNumber1->price + $pButterscotchSeaSalt->price + $pValonia->price; // 23000 + 29000 + 25000 = 77000
        $serviceFee = (int) ($subtotal * 0.05); // 3850
        $pbjtTax = (int) ($subtotal * 0.10); // 7700
        $totalAmount = $subtotal + $serviceFee + $pbjtTax; // 88550

        $demoOrder = Order::create([
            'order_number' => '#FH-0128',
            'table_id' => $table08->id,
            'table_number' => 'Meja 08',
            'customer_name' => 'Dina',
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'status' => 'cooking', // Sedang diseduh / dibuat barista
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'pbjt_tax' => $pbjtTax,
            'total_amount' => $totalAmount,
            'gateway_reference' => 'QRIS-DEMO-FH-0128',
            'paid_at' => now()->subMinutes(6),
            'eta_range' => '5–10 menit',
            'payment_time' => now()->subMinutes(6)->format('H.i'),
            'cooking_time' => now()->subMinutes(4)->format('H.i'),
        ]);

        OrderItem::create([
            'order_id' => $demoOrder->id,
            'product_id' => $pNumber1->id,
            'product_name' => $pNumber1->name,
            'price' => $pNumber1->price,
            'quantity' => 1,
            'options_selected' => ['Normal Ice', 'Less Sweet (50%)', 'Fresh Milk (Susu Sapi)'],
            'kitchen_note' => 'Gula less sweet ya kak.',
            'subtotal' => $pNumber1->price,
        ]);

        OrderItem::create([
            'order_id' => $demoOrder->id,
            'product_id' => $pButterscotchSeaSalt->id,
            'product_name' => $pButterscotchSeaSalt->name,
            'price' => $pButterscotchSeaSalt->price,
            'quantity' => 1,
            'options_selected' => ['Normal Ice', 'Normal Sweet (100%)'],
            'kitchen_note' => 'Foam sea salt dibanyakin.',
            'subtotal' => $pButterscotchSeaSalt->price,
        ]);

        OrderItem::create([
            'order_id' => $demoOrder->id,
            'product_id' => $pValonia->id,
            'product_name' => $pValonia->name,
            'price' => $pValonia->price,
            'quantity' => 1,
            'options_selected' => ['Normal Ice'],
            'kitchen_note' => null,
            'subtotal' => $pValonia->price,
        ]);

        Payment::create([
            'order_id' => $demoOrder->id,
            'payment_code' => 'PAY-FH-0128',
            'gateway' => 'qris_dynamic',
            'amount' => $totalAmount,
            'status' => 'success',
            'qris_payload' => '00020101021226600016ID.CO.QRIS.WWW01189360099900000000020215ID102002480000005204581253033605400885505802ID5913FOUNTAIN HIDE6007JAKARTA61051219062160112FH0128MEJA086304',
            'expires_at' => now()->addMinutes(15),
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
