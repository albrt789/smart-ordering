<?php

namespace App\Services;

use App\Models\Product;
use App\Models\RestaurantSetting;
use Illuminate\Support\Str;

class AiAssistantService
{
    /**
     * Answer questions with strict Scope Restriction.
     */
    public function reply(string $userPrompt, string $table = 'Meja 08'): array
    {
        $prompt = trim($userPrompt);
        $lower = strtolower($prompt);

        // 1. Check if the prompt is within the permitted restaurant domain
        if (!$this->isWithinScope($lower)) {
            return [
                'text' => 'Pertanyaan tidak sesuai.',
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        $setting = RestaurantSetting::getSettings();
        $products = Product::where('is_available', true)->with('category')->get();

        // 2. Location / Address Query
        if (str_contains($lower, 'lokasi') || str_contains($lower, 'alamat') || str_contains($lower, 'dimana restoran') || str_contains($lower, 'tempat')) {
            return [
                'text' => "Restoran {$setting->name} ({$setting->branch}) beralamat di: {$setting->address}.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // 3. Operating Hours Query
        if (str_contains($lower, 'jam buka') || str_contains($lower, 'jam operasional') || str_contains($lower, 'buka jam') || str_contains($lower, 'tutup jam')) {
            return [
                'text' => "Jam operasional {$setting->name} adalah {$setting->open_hours}. Kami melayani makan di tempat (dine-in) dengan pemesanan langsung dari meja.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // 4. Contact Information Query
        if (str_contains($lower, 'kontak') || str_contains($lower, 'nomor') || str_contains($lower, 'telepon') || str_contains($lower, 'wa') || str_contains($lower, 'email')) {
            return [
                'text' => "Anda dapat menghubungi layanan pelanggan {$setting->name} melalui Telepon/WhatsApp: {$setting->phone} atau Email: {$setting->email}.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // 5. Restaurant Profile Query
        if (str_contains($lower, 'profil') || str_contains($lower, 'tentang restoran') || str_contains($lower, 'tentang suap') || str_contains($lower, 'apa itu suap')) {
            return [
                'text' => "{$setting->name} ({$setting->branch}) — {$setting->description} Setiap meja dilengkapi QR Code unik untuk kemudahan pemesanan mandiri dan pembayaran QRIS tanpa antre.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // 6. Allergens & Composition Query
        if (str_contains($lower, 'alergi') || str_contains($lower, 'komposisi') || str_contains($lower, 'bahan') || str_contains($lower, 'kandungan') || str_contains($lower, 'susu') || str_contains($lower, 'kacang') || str_contains($lower, 'gluten') || str_contains($lower, 'udang') || str_contains($lower, 'nabati') || str_contains($lower, 'vegan')) {
            // Check specific dish mentioned
            foreach ($products as $prod) {
                if (str_contains($lower, strtolower($prod->name)) || str_contains($lower, strtolower(explode(' ', $prod->name)[0]))) {
                    $compositionInfo = $prod->composition ? " Komposisi: {$prod->composition}." : "";
                    $allergenInfo = $prod->allergens ? " Info Alergen: {$prod->allergens}" : "";
                    return [
                        'text' => "Untuk hidangan {$prod->name}:{$compositionInfo}{$allergenInfo}",
                        'has_allergen_warning' => true,
                        'product_id' => $prod->id,
                        'show_cart_ready_card' => false,
                    ];
                }
            }

            // General vegan / plant based query (matching Screenshot 3)
            if (str_contains($lower, 'nabati') || str_contains($lower, 'vegan') || str_contains($lower, 'tanpa susu')) {
                $jamur = $products->firstWhere('slug', 'jamur-crispy');
                return [
                    'text' => 'Jamur Crispy cocok buat camilan nabati. Sesuai info bahan restoran, adonannya tanpa susu dan kacang. Mengandung gluten dari tepung terigu.',
                    'has_allergen_warning' => true,
                    'product_id' => $jamur ? $jamur->id : null,
                    'show_cart_ready_card' => false,
                ];
            }

            return [
                'text' => 'Restoran kami menggunakan bahan-bahan pilihan. Menu Ayam Sambal Matah mengandung terasi udang & kedelai; Mie Ayam Suap & Jamur Crispy mengandung gluten terigu. Jika memiliki alergi berat, mohon informasikan tim kasir/restoran kami.',
                'has_allergen_warning' => true,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // 7. Cooking / Preparation Time Estimate Query
        if (str_contains($lower, 'waktu') || str_contains($lower, 'estimasi') || str_contains($lower, 'berapa menit') || str_contains($lower, 'lama pembuatan') || str_contains($lower, 'berapa lama')) {
            return [
                'text' => 'Estimasi waktu penyiapan makanan kami berkisar antara 10–20 menit untuk hidangan utama (seperti Ayam Sambal Matah & Mie Ayam), serta 3–5 menit untuk aneka minuman segar.',
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // 8. Menu Recommendations Query
        if (str_contains($lower, 'rekomendasi') || str_contains($lower, 'paling laris') || str_contains($lower, 'favorit') || str_contains($lower, 'enak') || str_contains($lower, 'pedas') || str_contains($lower, 'minuman') || str_contains($lower, 'menu')) {
            if (str_contains($lower, 'pedas') || str_contains($lower, 'sambal')) {
                $ayam = $products->firstWhere('slug', 'ayam-sambal-matah');
                return [
                    'text' => 'Rekomendasi pedas segar favorit: Ayam Sambal Matah (ayam panggang juicy dengan sambal matah harum jeruk limau) atau Nasi Gila Cikini.',
                    'has_allergen_warning' => false,
                    'product_id' => $ayam ? $ayam->id : null,
                    'show_cart_ready_card' => false,
                ];
            }

            if (str_contains($lower, 'minum') || str_contains($lower, 'haus') || str_contains($lower, 'segar')) {
                $es = $products->firstWhere('slug', 'es-teh-leci');
                return [
                    'text' => 'Minuman terlaris pelepas dahaga: Es Teh Leci dengan buah leci asli dan aroma teh melati premium, atau Kopi Susu Suap gula aren.',
                    'has_allergen_warning' => false,
                    'product_id' => $es ? $es->id : null,
                    'show_cart_ready_card' => false,
                ];
            }

            $topMenu = $products->where('is_signature', true)->first() ?? $products->first();
            return [
                'text' => "Menu signature andalan kami adalah {$topMenu->name} (Rp" . number_format($topMenu->price, 0, ',', '.') . ") dengan cita rasa otentik bumbu Nusantara.",
                'has_allergen_warning' => false,
                'product_id' => $topMenu ? $topMenu->id : null,
                'show_cart_ready_card' => false,
            ];
        }

        // 9. Ordering & Delivery Flow (Table Dine-In)
        if (str_contains($lower, 'pesan') || str_contains($lower, 'bayar') || str_contains($lower, 'qris') || str_contains($lower, 'kasir') || str_contains($lower, 'antar') || str_contains($lower, 'tambah')) {
            $tableNum = str_replace('Meja ', '', $table);
            return [
                'text' => "Pemesanan dilakukan langsung dari mejamu. Setelah memesan, Anda dapat membayar menggunakan QRIS dinamis di smartphone atau membayar tunai ke kasir. Pesanan yang lunas akan langsung kami antar ke {$table}. ✨",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => true,
            ];
        }

        // Default restaurant greeting for valid context
        return [
            'text' => "Halo! Aku asisten pintar Suap untuk {$table}. Kamu dapat bertanya tentang bahan menu, rekomendasi hidangan, info alergi, waktu pembuatan, jam buka, hingga lokasi restoran.",
            'has_allergen_warning' => false,
            'product_id' => null,
            'show_cart_ready_card' => false,
        ];
    }

    /**
     * Strict Scope Validator
     */
    private function isWithinScope(string $lower): bool
    {
        $allowedKeywords = [
            'menu', 'makanan', 'makan', 'minuman', 'minum', 'camilan', 'harga', 'ayam', 'jamur', 'teh', 'mie', 'kopi', 'nasi', 'tahu',
            'sambal', 'matah', 'crispy', 'leci', 'pedas', 'rekomendasi', 'komposisi', 'bahan', 'alergi', 'susu',
            'kacang', 'gluten', 'terigu', 'udang', 'terasi', 'nabati', 'vegan', 'gula', 'es', 'porsi', 'waktu',
            'estimasi', 'lama', 'menit', 'jam', 'buka', 'tutup', 'operasional', 'lokasi', 'alamat', 'dimana',
            'cikini', 'suap', 'restoran', 'profil', 'tentang', 'kontak', 'telepon', 'whatsapp', 'wa', 'email',
            'pesan', 'order', 'meja', 'bayar', 'qris', 'tunai', 'cash', 'kasir', 'status', 'dapur', 'antar',
            'struk', 'keranjang', 'tambah', 'kurang', 'halo', 'hai', 'pagi', 'siang', 'malam', 'bantuan', 'tolong'
        ];

        foreach ($allowedKeywords as $kw) {
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $lower)) {
                return true;
            }
        }

        return false;
    }
}
