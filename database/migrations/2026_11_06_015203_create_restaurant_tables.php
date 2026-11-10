<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Restaurant Settings
        Schema::create('restaurant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Fountain Hide');
            $table->string('branch')->default('Fountain Hide Coffee & Roastery');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('open_hours')->default('08:00 - 23:00 WIB');
            $table->decimal('service_fee_pct', 5, 2)->default(5.00); // 5%
            $table->decimal('pbjt_tax_pct', 5, 2)->default(10.00); // 10%
            $table->text('description')->nullable();
            $table->string('logo')->default('/images/logo.png');
            $table->timestamps();
        });

        // 2. Tables (Meja Restoran)
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_number')->unique(); // Meja 01, Meja 02, etc.
            $table->string('qr_token')->unique(); // unique token for QR scan
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Categories (Kategori Menu)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. Products (Daftar Makanan & Minuman)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('price');
            $table->string('tag')->nullable(); // PALING LARIS, NABATI, SEGAR, FAVORIT
            $table->string('category_path')->nullable(); // MAKANAN / SIGNATURE
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->text('composition')->nullable(); // Komposisi & bahan makanan
            $table->string('image')->nullable();
            $table->json('badges')->nullable(); // ['Pedas sedang', '1 porsi', '15-20 menit']
            $table->text('allergens')->nullable();
            $table->boolean('is_signature')->default(false);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // 5. Product Options (Modifier Group: misal Pilih Nasi, Sambal, Saus)
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name'); // Pilih nasi, Sambalnya gimana?, etc.
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        // 6. Product Choices (Pilihan dalam Modifier Group)
        Schema::create('product_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained('product_options')->onDelete('cascade');
            $table->string('name'); // Nasi putih, Nasi merah, Sambal dipisah
            $table->integer('extra_price')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 7. Orders (Pesanan Utama)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->index(); // #SP-0248
            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->string('table_number')->default('Meja 08');
            $table->string('customer_name')->default('Tamu');
            
            // Pemisahan tegas: Status Pembayaran vs Status Pengerjaan
            $table->enum('payment_method', ['qris', 'cash'])->default('qris');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->enum('status', [
                'waiting_payment',  // 1. Menunggu pembayaran
                'paid',             // 2. Pembayaran berhasil
                'received',         // 3. Pesanan diterima
                'cooking',          // 4. Sedang dibuat
                'preparing',        // 5. Sedang disiapkan
                'delivering',       // 6. Sedang diantarkan
                'completed',        // 7. Selesai
                'cancelled'         // Dibatalkan
            ])->default('waiting_payment');

            $table->integer('subtotal')->default(0);
            $table->integer('service_fee')->default(0);
            $table->integer('pbjt_tax')->default(0);
            $table->integer('total_amount')->default(0);
            
            $table->string('gateway_reference')->nullable()->index(); // Midtrans/Xendit transaction ID
            $table->timestamp('paid_at')->nullable();
            $table->string('eta_range')->nullable(); // 12.50–12.55
            $table->string('payment_time')->nullable(); // 12.31
            $table->string('cooking_time')->nullable(); // 12.33
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete(); // Kasir yang konfirmasi tunai
            $table->timestamps();
        });

        // 8. Order Items (Rincian Menu yang Dipesan)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->integer('price'); // Harga per unit saat transaksi
            $table->integer('quantity');
            $table->json('options_selected')->nullable(); // [{group: 'Pilih nasi', choice: 'Nasi putih', extra: 0}]
            $table->string('kitchen_note')->nullable(); // "Ayamnya dipotong kecil, ya."
            $table->integer('subtotal');
            $table->timestamps();
        });

        // 9. Payments (Transaksi Pembayaran & Dynamic QRIS)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('payment_code')->unique()->index();
            $table->enum('gateway', ['qris_dynamic', 'cash'])->default('qris_dynamic');
            $table->integer('amount');
            $table->enum('status', ['pending', 'success', 'expired', 'failed'])->default('pending');
            $table->longText('qris_payload')->nullable(); // Raw QRIS payload string
            $table->json('raw_response')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 10. Payment Logs (Audit Webhook & Verifikasi Pembayaran)
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('event'); // webhook_received, payment_success, signature_failed, etc.
            $table->json('payload')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        // 11. AI Messages (Percakapan Suap AI)
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->string('table_number')->default('Meja 08');
            $table->string('sender'); // user, ai
            $table->text('text');
            $table->string('time');
            $table->boolean('has_allergen_warning')->default(false);
            $table->unsignedBigInteger('product_id')->nullable();
            $table->boolean('show_cart_ready_card')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('product_choices');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('restaurant_settings');
    }
};
