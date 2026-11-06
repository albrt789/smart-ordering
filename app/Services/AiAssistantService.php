<?php

namespace App\Services;

use App\Models\Product;
use App\Models\RestaurantSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    protected string $provider;
    protected string $model;
    protected ?string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected string $reasoningEffort;

    public function __construct()
    {
        $this->provider = strtolower((string) (config('services.ai.provider') ?: 'groq'));
        $this->model = (string) (config('services.ai.model') ?: 'openai/gpt-oss-120b');
        $this->apiKey = config('services.ai.api_key');
        $this->baseUrl = rtrim((string) (config('services.ai.base_url') ?: 'https://api.groq.com/openai/v1'), '/');
        $this->timeout = (int) config('services.ai.timeout', 20);
        $this->reasoningEffort = (string) config('services.ai.reasoning_effort', 'low');
    }

    public function getActiveModel(): string
    {
        return $this->model;
    }

    public function getActiveProvider(): string
    {
        return $this->provider;
    }

    /**
     * Answer questions with strict Scope Restriction.
     * Uses Groq's "openai/gpt-oss-120b" (configurable) with intelligent local fallback.
     */
    public function reply(string $userPrompt, string $table = 'Meja 08'): array
    {
        $prompt = trim($userPrompt);
        $lower = strtolower($prompt);

        // 1. Strict Fast Guardrail: Check if the prompt is outside permitted restaurant domain
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

        // 2. Query Groq (model "openai/gpt-oss-120b") if API key is provided
        if (!empty($this->apiKey)) {
            try {
                $llmResult = $this->queryLlmModel($prompt, $table, $setting, $products);
                if ($llmResult !== null) {
                    return $llmResult;
                }
            } catch (\Exception $e) {
                Log::warning("AI LLM call ({$this->model}) fallback to local engine: " . $e->getMessage());
            }
        }

        // 3. Fallback: Local database-backed intelligent restaurant engine
        return $this->localEngineReply($lower, $table, $setting, $products);
    }

    /**
     * Query Groq's OpenAI-compatible endpoint for "openai/gpt-oss-120b".
     */
    protected function queryLlmModel(string $userPrompt, string $table, RestaurantSetting $setting, $products): ?array
    {
        $systemPrompt = $this->buildSystemPrompt($setting, $products, $table);

        $request = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout($this->timeout);

        // Header atribusi hanya diperlukan OpenRouter, bukan Groq.
        if ($this->provider === 'openrouter') {
            $request = $request->withHeaders([
                'HTTP-Referer' => url('/'),
                'X-Title' => 'SUAP Smart Ordering Restaurant AI',
            ]);
        }

        $payload = [
            'model' => $this->model, // 'openai/gpt-oss-120b'
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt,
                ],
            ],
            'temperature' => 0.5,
        ];

        if ($this->provider === 'groq') {
            $payload['max_completion_tokens'] = 600;

            // gpt-oss mendukung reasoning_effort; "low" menjaga respons tetap cepat.
            if ($this->reasoningEffort !== '') {
                $payload['reasoning_effort'] = $this->reasoningEffort;
            }
        } else {
            $payload['max_tokens'] = 600;
        }

        $response = $request->post($this->baseUrl . '/chat/completions', $payload);

        if (!$response->successful()) {
            Log::warning("LLM API request failed [{$response->status()}]: " . $response->body());
            return null;
        }

        $replyText = trim($response->json('choices.0.message.content', ''));
        if (empty($replyText)) {
            return null;
        }

        // Strict enforcement: if model detects out of scope
        if (str_contains(strtolower($replyText), 'pertanyaan tidak sesuai')) {
            return [
                'text' => 'Pertanyaan tidak sesuai.',
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Detect if any product from menu was mentioned or recommended
        $foundProductId = null;
        foreach ($products as $prod) {
            if (stripos($replyText, $prod->name) !== false) {
                $foundProductId = $prod->id;
                break;
            }
        }

        // Check if allergen warning is relevant
        $hasAllergenWarning = false;
        $allergenKeywords = ['alergi', 'kacang', 'susu', 'gluten', 'terigu', 'udang', 'terasi', 'telur'];
        foreach ($allergenKeywords as $akw) {
            if (stripos($replyText, $akw) !== false || stripos($userPrompt, $akw) !== false) {
                $hasAllergenWarning = true;
                break;
            }
        }

        return [
            'text' => $replyText,
            'has_allergen_warning' => $hasAllergenWarning,
            'product_id' => $foundProductId,
            'show_cart_ready_card' => stripos($userPrompt, 'pesan') !== false || stripos($userPrompt, 'tambah') !== false,
        ];
    }

    /**
     * Build rich system prompt with database menu, allergens, and strict scope restriction
     */
    protected function buildSystemPrompt(RestaurantSetting $setting, $products, string $table): string
    {
        $menuList = [];
        foreach ($products as $p) {
            $cat = $p->category->name ?? 'Menu';
            $price = number_format($p->price, 0, ',', '.');
            $stockInfo = $p->track_stock ? "Stok: {$p->stock} porsi" : "Stok: tersedia";
            $menuList[] = "- {$p->name} ({$cat}, Rp{$price}) | Deskripsi: {$p->description} | Komposisi: {$p->composition} | Alergen: {$p->allergens} | Waktu masak: {$p->prep_time_minutes} mnt | {$stockInfo}";
        }
        $menuText = implode("\n", $menuList);

        return <<<PROMPT
Anda adalah "Suap AI", asisten informasi resmi restoran SUAP. Cikini untuk melayani pelanggan di {$table}.

TUGAS ANDA:
1. Menjawab komposisi, bahan baku, dan informasi alergen makanan/minuman.
2. Memberikan rekomendasi menu berdasarkan data menu yang tersedia.
3. Memberikan estimasi waktu pembuatan makanan/minuman.
4. Memberikan informasi lokasi, jam operasional, kontak, dan profil restoran.
5. Menjelaskan alur pemesanan mandiri & pembayaran QRIS dinamis langsung dari meja.

DATA RESMI RESTORAN:
- Nama: {$setting->name} ({$setting->branch})
- Profil: {$setting->description}
- Alamat: {$setting->address}
- Jam Operasional: {$setting->open_hours}
- Kontak: Telepon/WA: {$setting->phone}, Email: {$setting->email}

DAFTAR MENU RESMI DARI DATABASE:
{$menuText}

ATURAN WAJIB & PEMBATASAN TOPIK (STRICT SCOPE RESTRICTION):
Jika pelanggan bertanya hal di luar informasi restoran, menu, alergen, dan layanan pemesanan makanan (misalnya tentang politik, cuaca dunia, coding, matematika, pengetahuan umum, dll), ANDA WAJIB MENJAWAB HANYA DENGAN KALIMAT PERSIS:
"Pertanyaan tidak sesuai."
Jangan memberikan jawaban atau penjelasan apa pun untuk topik di luar restoran.
Jawablah dengan ramah, sopan, dan ringkas dalam Bahasa Indonesia.
PROMPT;
    }

    /**
     * Fallback database-backed local engine
     */
    protected function localEngineReply(string $lower, string $table, RestaurantSetting $setting, $products): array
    {
        // Location / Address Query
        if (str_contains($lower, 'lokasi') || str_contains($lower, 'alamat') || str_contains($lower, 'dimana restoran') || str_contains($lower, 'tempat')) {
            return [
                'text' => "Restoran {$setting->name} ({$setting->branch}) beralamat di: {$setting->address}.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Operating Hours Query
        if (str_contains($lower, 'jam buka') || str_contains($lower, 'jam operasional') || str_contains($lower, 'buka jam') || str_contains($lower, 'tutup jam')) {
            return [
                'text' => "Jam operasional {$setting->name} adalah {$setting->open_hours}. Kami melayani makan di tempat (dine-in) dengan pemesanan langsung dari meja.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Contact Information Query
        if (str_contains($lower, 'kontak') || str_contains($lower, 'nomor') || str_contains($lower, 'telepon') || str_contains($lower, 'wa') || str_contains($lower, 'email')) {
            return [
                'text' => "Anda dapat menghubungi layanan pelanggan {$setting->name} melalui Telepon/WhatsApp: {$setting->phone} atau Email: {$setting->email}.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Restaurant Profile Query
        if (str_contains($lower, 'profil') || str_contains($lower, 'tentang restoran') || str_contains($lower, 'tentang suap') || str_contains($lower, 'apa itu suap')) {
            return [
                'text' => "{$setting->name} ({$setting->branch}) — {$setting->description} Setiap meja dilengkapi QR Code unik untuk kemudahan pemesanan mandiri dan pembayaran QRIS tanpa antre.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Allergens & Composition Query
        if (str_contains($lower, 'alergi') || str_contains($lower, 'komposisi') || str_contains($lower, 'bahan') || str_contains($lower, 'kandungan') || str_contains($lower, 'susu') || str_contains($lower, 'kacang') || str_contains($lower, 'gluten') || str_contains($lower, 'udang') || str_contains($lower, 'nabati') || str_contains($lower, 'vegan')) {
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

        // Cooking / Preparation Time Estimate Query
        if (str_contains($lower, 'waktu') || str_contains($lower, 'estimasi') || str_contains($lower, 'berapa menit') || str_contains($lower, 'lama pembuatan') || str_contains($lower, 'berapa lama')) {
            return [
                'text' => 'Estimasi waktu penyiapan makanan kami berkisar antara 10–20 menit untuk hidangan utama (seperti Ayam Sambal Matah & Mie Ayam), serta 3–5 menit untuk aneka minuman segar.',
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Menu Recommendations Query
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

        // Ordering & Delivery Flow (Table Dine-In)
        if (str_contains($lower, 'pesan') || str_contains($lower, 'bayar') || str_contains($lower, 'qris') || str_contains($lower, 'kasir') || str_contains($lower, 'antar') || str_contains($lower, 'tambah')) {
            return [
                'text' => "Pemesanan dilakukan langsung dari mejamu. Setelah memesan, Anda dapat membayar menggunakan QRIS dinamis di smartphone atau membayar tunai ke kasir. Pesanan yang lunas akan langsung kami antar ke {$table}. ✨",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => true,
            ];
        }

        // Default restaurant greeting
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
