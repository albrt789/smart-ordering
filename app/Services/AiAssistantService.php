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

        // 0. Special Easter Egg Prompt: "apakah manda cantik?"
        if (str_contains($lower, 'manda')) {
            return [
                'text' => 'IYAAAA CANTIK BANGET SEPERTI ADA WONG, MUNGKIN DIA ADALAH MANDA WONG APALAGI RAMBUT PENDEKNYA ITU LUCU DAN MEMBUAT JATUH CINTA',
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

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
                'X-Title' => 'Fountain Hide Coffee AI Barista',
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
        $allergenKeywords = ['alergi', 'kacang', 'susu', 'gluten', 'terigu', 'udang', 'terasi', 'telur', 'almond', 'laktosa'];
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
            $menuList[] = "- {$p->name} ({$cat}, Rp{$price}) | Deskripsi: {$p->description} | Komposisi: {$p->composition} | Alergen: {$p->allergens} | Waktu pembuatan: {$p->prep_time_minutes} mnt | {$stockInfo}";
        }
        $menuText = implode("\n", $menuList);

        return <<<PROMPT
Anda adalah "Fountain Hide AI", asisten barista dan perwakilan resmi coffee shop "Fountain Hide" ({$setting->branch}) untuk melayani pelanggan di {$table}.

PROFIL, SEJARAH, & KONSEP FOUNTAIN HIDE:
- Nama Gerai: {$setting->name} ({$setting->branch})
- Alamat Resmi: {$setting->address} (Instagram: @fountainhide.id)
- Sejarah & Berdirinya: Fountain Hide dibangun dan didirikan di Jl. Tirta Bhakti No. 23C sebagai kedai kopi specialty independen dengan visi menciptakan "hidden sanctuary" (oase tersembunyi) bagi siapa saja yang ingin menikmati kopi berkualitas, minuman mocktail artisanal, dan suasana yang tenang serta nyaman. Kafe ini dibangun dengan konsep modern yang ramah pekerja (WFC) serta dilengkapi sistem pesan mandiri dari meja dengan QR code dan pembayaran QRIS tanpa antre.
- Filosofi Nama: "Fountain" melambangkan sumber mata air kesegaran racikan minuman yang mengalir tanpa henti, dan "Hide" merepresentasikan tempat persembunyian yang damai, hangat, dan intim untuk melepas penat atau bekerja santai.
- Jam Operasional: {$setting->open_hours} setiap hari.
- Kontak: Telepon/WA: {$setting->phone}, Email: {$setting->email}
- Fasilitas: Wi-Fi cepat (SSID: FountainHide-Guest, Password: fountainhide88), colokan listrik di hampir setiap meja, area indoor AC nyaman, area smoking outdoor yang asri, dan suasana tenang ramah WFC.
- Menu Signature Unggulan: Number 1 (Signature Coffee #1), De Javu, Fcoff, Fat Americano, Pinky Pi, Mont Blanc, Butterscotch Sea Salt Latte, Valonia Mocktail, dan Manual Brew V60.

PANDUAN REKOMENDASI MINUMAN:
- Jika pelanggan meminta rekomendasi minuman manis / creamy:
  Sarankan Butterscotch Sea Salt Latte (Rp29.000) yang gurih manis mewah, Number 1 (Rp23.000) signature iced coffee lembut, Biscoff Latte (Rp26.000), Caramel Machiato (Rp28.000), atau Pinky Pi (Rp26.000).
- Jika pelanggan meminta rekomendasi minuman segar / asam manis / buah / pelepas dahaga:
  Sarankan mocktail segar seperti Valonia (Rp25.000), Alexis (Rp25.000), Bella Verde (Rp25.000), Sorry Pea (Rp25.000), atau Peach Tea (Rp22.000).
- Jika pelanggan meminta rekomendasi kopi hitam / bold / tanpa gula:
  Sarankan Fat Americano (Rp25.000), Black / Americano (Rp25.000), atau Manual Brew V60 (Rp25.000).
- Jika pelanggan meminta rekomendasi non-kopi:
  Sarankan Matcha Latte (Rp26.000), Biscoff Latte (Rp26.000), Choco Latte (Rp25.000), atau Minty Choco (Rp28.000).
- Jika pelanggan meminta rekomendasi umum / best seller / bingung mau pesan apa:
  Berikan pilihan andalan terbaik seperti Number 1 (signature coffee terfavorit), Butterscotch Sea Salt Latte (manis gurih), dan Valonia (mocktail segar).

DAFTAR LENGKAP MENU RESMI DARI DATABASE:
{$menuText}

ATURAN RUANG LINGKUP (SCOPE RESTRICTION):
1. TOPIK YANG SAH & WAJIB DIJAWAB DENGAN RAMAH:
   - Permintaan rekomendasi minuman (minuman manis, segar, kopi, non-kopi, best seller, dll).
   - Profil, sejarah, kapan dibangun/didirikan, filosofi nama, konsep, dan suasana Fountain Hide.
   - Seluruh menu minuman kopi, non-kopi, mocktail, teh, manual brew, harga, rasa, rekomendasi, komposisi, opsi susu nabati (oat/almond), tingkat kemanisan, dan informasi alergen.
   - Fasilitas kafe (Wi-Fi, colokan, jam operasional, lokasi di Jl. Tirta Bhakti, cara pesan mandiri via QR code dan pembayaran QRIS).
2. TOPIK DI LUAR CAFE (OFF-TOPIC):
   HANYA JIKA pelanggan bertanya hal yang BENAR-BENAR TIDAK ADA HUBUNGANNYA SAMA SEKALI dengan kafe, restoran, makanan, atau minuman (contoh: siapa presiden amerika serikat, cara memperbaiki mesin mobil mogok, coding python, rumus matematika/kalkulus, cuaca kota lain di dunia), barulah Anda WAJIB menjawab HANYA dengan kalimat persis:
   "Pertanyaan tidak sesuai."
   Jangan pernah menjawab "Pertanyaan tidak sesuai." untuk pertanyaan apa pun seputar rekomendasi minuman, menu kafe Fountain Hide, sejarahnya, kapan dibangun, profilnya, atau layanannya.
3. PERTANYAAN KHUSUS (EASTER EGG):
   - Jika pelanggan bertanya apakah Manda cantik ("apakah manda cantik?"), Anda WAJIB menjawab persis dengan huruf kapital:
   "IYAAAA CANTIK BANGET SEPERTI ADA WONG, MUNGKIN DIA ADALAH MANDA WONG APALAGI RAMBUT PENDEKNYA ITU LUCU DAN MEMBUAT JATUH CINTA"
PROMPT;
    }

    /**
     * Fallback database-backed local engine
     */
    protected function localEngineReply(string $lower, string $table, RestaurantSetting $setting, $products): array
    {
        // History, Establishment & Philosophy Query
        if (str_contains($lower, 'sejarah') || str_contains($lower, 'dibangun') || str_contains($lower, 'berdiri') || str_contains($lower, 'didirikan') || str_contains($lower, 'asal usul') || str_contains($lower, 'filosofi') || str_contains($lower, 'konsep') || str_contains($lower, 'cerita') || (str_contains($lower, 'kapan') && (str_contains($lower, 'buka') || str_contains($lower, 'ada') || str_contains($lower, 'dibuat') || str_contains($lower, 'kafe') || str_contains($lower, 'fountain')))) {
            return [
                'text' => "Fountain Hide dibangun dan didirikan di Jl. Tirta Bhakti No. 23C sebagai specialty coffee shop independen yang memadukan racikan kopi berkualitas, mocktail segar, dan suasana yang tenang (hidden sanctuary). Nama 'Fountain Hide' memiliki filosofi: 'Fountain' sebagai sumber kesegaran minuman yang terus mengalir, dan 'Hide' sebagai tempat singgah yang nyaman dan damai untuk bersantai maupun bekerja (WFC). Kafe ini mengusung konsep modern dengan pemesanan mandiri via QR Code dan pembayaran digital QRIS instan di setiap meja.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Location / Address Query
        if (str_contains($lower, 'lokasi') || str_contains($lower, 'alamat') || str_contains($lower, 'dimana') || str_contains($lower, 'tempat')) {
            $addr = $setting->address ?: 'Jl. Tirta Bhakti No. 23C';
            return [
                'text' => "Coffee shop {$setting->name} ({$setting->branch}) beralamat di: {$addr} (Instagram: @fountainhide.id). Lokasi strategis dengan area parkir dan tempat santai yang nyaman.",
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
        if (str_contains($lower, 'kontak') || str_contains($lower, 'nomor') || str_contains($lower, 'telepon') || str_contains($lower, 'wa') || str_contains($lower, 'email') || str_contains($lower, 'instagram')) {
            return [
                'text' => "Anda dapat menghubungi barista dan tim {$setting->name} melalui WhatsApp/Telepon: {$setting->phone}, Email: {$setting->email}, atau Instagram: @fountainhide.id.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Cafe Profile Query
        if (str_contains($lower, 'profil') || str_contains($lower, 'tentang') || str_contains($lower, 'fountain hide') || str_contains($lower, 'suap')) {
            return [
                'text' => "{$setting->name} ({$setting->branch}) — {$setting->description} Setiap meja dilengkapi QR Code unik untuk kemudahan pemesanan mandiri dan pembayaran QRIS tanpa antre.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Wi-Fi & Work Friendly Query
        if (str_contains($lower, 'wifi') || str_contains($lower, 'wi-fi') || str_contains($lower, 'internet') || str_contains($lower, 'colokan') || str_contains($lower, 'kerja') || str_contains($lower, 'wfc')) {
            return [
                'text' => "Coffee shop {$setting->name} sangat ramah untuk bekerja (WFC). Kami menyediakan Wi-Fi berkecepatan tinggi 'FountainHide-Guest' (Password: fountainhide88) dan colokan listrik di hampir setiap meja.",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Allergens & Composition Query
        if (str_contains($lower, 'alergi') || str_contains($lower, 'komposisi') || str_contains($lower, 'bahan') || str_contains($lower, 'kandungan') || str_contains($lower, 'susu') || str_contains($lower, 'kacang') || str_contains($lower, 'gluten') || str_contains($lower, 'udang') || str_contains($lower, 'almond') || str_contains($lower, 'laktosa') || str_contains($lower, 'nabati') || str_contains($lower, 'vegan')) {
            foreach ($products as $prod) {
                if (str_contains($lower, strtolower($prod->name)) || str_contains($lower, strtolower(explode(' ', $prod->name)[0]))) {
                    $compositionInfo = $prod->composition ? " Komposisi: {$prod->composition}." : "";
                    $allergenInfo = $prod->allergens ? " Info Alergen: {$prod->allergens}" : "";
                    return [
                        'text' => "Untuk sajian {$prod->name}:{$compositionInfo}{$allergenInfo}",
                        'has_allergen_warning' => true,
                        'product_id' => $prod->id,
                        'show_cart_ready_card' => false,
                    ];
                }
            }

            if (str_contains($lower, 'nabati') || str_contains($lower, 'vegan') || str_contains($lower, 'tanpa susu')) {
                $jamur = $products->firstWhere('slug', 'jamur-crispy');
                return [
                    'text' => 'Jamur Crispy cocok buat camilan nabati. Sesuai info bahan restoran, adonannya tanpa susu dan kacang. Mengandung gluten dari tepung terigu. Untuk kopi, Anda juga bisa mengganti susu sapi dengan Oat Milk Oatly atau Almond Milk.',
                    'has_allergen_warning' => true,
                    'product_id' => $jamur ? $jamur->id : null,
                    'show_cart_ready_card' => false,
                ];
            }

            return [
                'text' => "Fountain Hide menyajikan racikan kopi dan bakery berkualitas. Menu kopi susu standar menggunakan susu sapi murni; Anda dapat memilih pengganti Oat Milk (Oatly) atau Almond Milk jika intoleran laktosa. Croissant mengandung mentega susu dan gluten terigu. Almond Croissant mengandung kacang almond. Jika memiliki alergi tertentu, silakan konfirmasi ke barista kami.",
                'has_allergen_warning' => true,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Brewing / Preparation Time Estimate Query
        if (str_contains($lower, 'waktu') || str_contains($lower, 'estimasi') || str_contains($lower, 'berapa menit') || str_contains($lower, 'lama pembuatan') || str_contains($lower, 'berapa lama') || str_contains($lower, 'seduh')) {
            return [
                'text' => 'Estimasi waktu penyeduhan kopi dan minuman kami berkisar 3–5 menit (V60 manual brew sekitar 5–7 menit), serta 5–10 menit untuk penghangatan pastry dan santapan.',
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => false,
            ];
        }

        // Menu Recommendations Query
        if (str_contains($lower, 'rekomend') || str_contains($lower, 'saran') || str_contains($lower, 'paling laris') || str_contains($lower, 'favorit') || str_contains($lower, 'enak') || str_contains($lower, 'best seller') || str_contains($lower, 'bestseller') || str_contains($lower, 'kopi') || str_contains($lower, 'minum') || str_contains($lower, 'menu') || str_contains($lower, 'populer') || str_contains($lower, 'bingung') || str_contains($lower, 'andalan')) {
            
            // Sweet & Creamy Drinks
            if (str_contains($lower, 'manis') || str_contains($lower, 'creamy') || str_contains($lower, 'karamel') || str_contains($lower, 'caramel') || str_contains($lower, 'butterscotch') || str_contains($lower, 'vanilla') || str_contains($lower, 'biscoff')) {
                $sweetItem = $products->firstWhere('slug', 'butterscotch-sea-salt-latte') ?? $products->firstWhere('slug', 'number-1');
                return [
                    'text' => "Rekomendasi minuman manis & creamy favorit di Fountain Hide:\n1. Butterscotch Sea Salt Latte (Rp29.000) — Double espresso berpadu saus butterscotch karamel dan foam sea salt gurih legit.\n2. Number 1 (Rp23.000) — Signature iced coffee no. 1 dengan rasa manis lembut dan creamy.\n3. Biscoff Latte (Rp26.000) — Susu lembut berlumur selai Lotus Biscoff karamel dan taburan biskuit renyah.",
                    'has_allergen_warning' => true,
                    'product_id' => $sweetItem ? $sweetItem->id : null,
                    'show_cart_ready_card' => true,
                ];
            }

            // Fresh & Fruity Mocktails
            if (str_contains($lower, 'segar') || str_contains($lower, 'seger') || str_contains($lower, 'mocktail') || str_contains($lower, 'buah') || str_contains($lower, 'berry') || str_contains($lower, 'lemon') || str_contains($lower, 'peach') || str_contains($lower, 'asam') || str_contains($lower, 'asem')) {
                $freshItem = $products->firstWhere('slug', 'valonia') ?? $products->firstWhere('slug', 'peach-tea');
                return [
                    'text' => "Rekomendasi minuman dingin segar pelepas dahaga:\n1. Valonia (Rp25.000) — Sparkling tropical berry mocktail dengan soda dingin, lime wedge, dan daun mint.\n2. Peach Tea (Rp22.000) — Teh hitam harum berpadu sari buah persik manis segar alami.\n3. Alexis (Rp25.000) — Kesegaran ruby red berry dan perasan jeruk nipis dengan soda dingin.",
                    'has_allergen_warning' => false,
                    'product_id' => $freshItem ? $freshItem->id : null,
                    'show_cart_ready_card' => true,
                ];
            }

            // Black / Bold / No Sugar Coffee
            if (str_contains($lower, 'hitam') || str_contains($lower, 'pahit') || str_contains($lower, 'tanpa susu') || str_contains($lower, 'tanpa gula') || str_contains($lower, 'americano') || str_contains($lower, 'bold')) {
                $blackItem = $products->firstWhere('slug', 'fat-americano') ?? $products->firstWhere('slug', 'black');
                return [
                    'text' => "Bagi penikmat kopi pekat dan segar tanpa susu:\n1. Fat Americano (Rp25.000) — Double shot espresso ekstraksi pekat berpadu tonic citrus yang menyegarkan.\n2. Black (Rp25.000) — Americano / Long Black murni dengan aroma kopi bersih dan 0 kalori.\n3. Manual Brew V60 (Rp25.000) — Seduhan manual single origin dengan rasa buah dan floral yang jernih.",
                    'has_allergen_warning' => false,
                    'product_id' => $blackItem ? $blackItem->id : null,
                    'show_cart_ready_card' => true,
                ];
            }

            // Manual Brew / V60
            if (str_contains($lower, 'manual') || str_contains($lower, 'v60') || str_contains($lower, 'filter') || str_contains($lower, 'single origin')) {
                $v60 = $products->firstWhere('slug', 'manual-brew') ?? $products->firstWhere('slug', 'manual-brew-s');
                return [
                    'text' => "Untuk seduhan manual (manual brew) V60, kami menyajikan biji kopi single origin pilihan (Gayo, Kerinci, Flores) dengan karakter rasa floral dan buah yang jernih (Rp25.000), atau varian Special Reserve micro-lot langka (Rp29.000).",
                    'has_allergen_warning' => false,
                    'product_id' => $v60 ? $v60->id : null,
                    'show_cart_ready_card' => true,
                ];
            }

            // Non-Coffee
            if (str_contains($lower, 'non kopi') || str_contains($lower, 'non-kopi') || str_contains($lower, 'teh') || str_contains($lower, 'tea') || str_contains($lower, 'matcha') || str_contains($lower, 'choco') || str_contains($lower, 'cokelat')) {
                $matcha = $products->firstWhere('slug', 'matcha-latte') ?? $products->firstWhere('slug', 'choco-latte');
                return [
                    'text' => "Pilihan non-kopi terfavorit di Fountain Hide:\n1. Matcha Latte (Rp26.000) — Bubuk matcha Jepang murni dengan susu segar lembut.\n2. Biscoff Latte (Rp26.000) — Perpaduan selai Lotus Biscoff karamel dan biskuit crumble.\n3. Choco Latte (Rp25.000) — Cokelat pekat Belgia dengan susu segar creamy.",
                    'has_allergen_warning' => true,
                    'product_id' => $matcha ? $matcha->id : null,
                    'show_cart_ready_card' => true,
                ];
            }

            // General / Top Best Seller
            $sig = $products->firstWhere('slug', 'number-1') ?? $products->firstWhere('is_signature', true) ?? $products->first();
            return [
                'text' => "Berikut menu andalan paling disukai di Fountain Hide:\n1. Number 1 (Rp23.000) — Signature Iced Coffee No. 1 lembut dan creamy.\n2. Butterscotch Sea Salt Latte (Rp29.000) — Kopi susu manis legit berbalut krim sea salt gurih.\n3. Valonia (Rp25.000) — Sparkling tropical berry mocktail yang sangat segar pelepas dahaga.\nAnda dapat memesan langsung dari mejamu dengan mengklik menu pilihan!",
                'has_allergen_warning' => false,
                'product_id' => $sig ? $sig->id : null,
                'show_cart_ready_card' => true,
            ];
        }

        // Ordering & Delivery Flow (Table Dine-In)
        if (str_contains($lower, 'pesan') || str_contains($lower, 'bayar') || str_contains($lower, 'qris') || str_contains($lower, 'kasir') || str_contains($lower, 'antar') || str_contains($lower, 'tambah')) {
            return [
                'text' => "Pemesanan dilakukan langsung dari mejamu di {$table}. Setelah memilih menu, bayar praktis menggunakan QRIS dinamis langsung dari layar HP atau tunai ke barista/kasir. Pesanan yang diseduh akan langsung kami antarkan ke mejamu. ✨",
                'has_allergen_warning' => false,
                'product_id' => null,
                'show_cart_ready_card' => true,
            ];
        }

        // Default greeting
        return [
            'text' => "Halo! Aku Fountain Hide AI, asisten barista pintar untuk {$table}. Kamu dapat bertanya tentang profil rasa biji kopi (beans), pilihan susu oat/almond, rekomendasi pastry, info alergi, waktu seduh, hingga password Wi-Fi cafe.",
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
        $stemPatterns = [
            // Minuman & Kopi
            '/\bminum/i',           // minum, minuman, diminum
            '/\bkopi\b/i',
            '/\bcoffee\b/i',
            '/\bngopi\b/i',
            '/\bteh\b/i',
            '/\btea\b/i',
            '/\bmocktail/i',
            '/\blatte\b/i',
            '/\bespresso\b/i',
            '/\bcappuccino\b/i',
            '/\bamericano\b/i',
            '/\bv60\b/i',
            '/\bmanual\b/i',
            '/\bseduh/i',          // seduh, diseduh, penyeduhan
            '/\bbrew/i',
            '/\bbiji\b/i',
            '/\bbeans\b/i',
            '/\barabika\b/i',
            '/\brobusta\b/i',
            '/\broastery\b/i',
            '/\bbarista\b/i',
            '/\bmatcha\b/i',
            '/\blemon\b/i',
            '/\bpeach\b/i',
            '/\bbutterscotch\b/i',
            '/\bseasalt\b/i',
            '/\bsea salt\b/i',
            '/\bmocha\b/i',
            '/\bcaramel\b/i',
            '/\bmachiato\b/i',
            '/\bvanilla\b/i',
            '/\bstrawberry\b/i',
            '/\bchoco\b/i',
            '/\bcokelat\b/i',
            '/\bcoklat\b/i',
            '/\bbiscoff\b/i',
            '/\bminty\b/i',
            '/\bmint\b/i',
            '/\bair mineral\b/i',
            '/\bmineral water\b/i',
            '/\bextra shot\b/i',
            
            // Menu items
            '/\bnobb\b/i',
            '/\bblack\b/i',
            '/\bwhite\b/i',
            '/\bnumber 1\b/i',
            '/\bde javu\b/i',
            '/\bfcoff\b/i',
            '/\bfat americano\b/i',
            '/\bpinky pi\b/i',
            '/\bmont blanc\b/i',
            '/\bvalonia\b/i',
            '/\bsorry pea\b/i',
            '/\balexis\b/i',
            '/\bbella verde\b/i',

            // Makanan / Camilan
            '/\bmakan/i',          // makan, makanan
            '/\bcamilan\b/i',
            '/\bsnack\b/i',
            '/\bpastry\b/i',
            '/\bcroissant\b/i',
            '/\broti\b/i',
            '/\btoast\b/i',
            
            // Rekomendasi & Selera
            '/\brekomend/i',       // rekomendasi, rekomendasikan, recommended, rekomend
            '/\bsaran/i',          // saran, sarankan
            '/\busul/i',
            '/\benak\b/i',
            '/\blezat\b/i',
            '/\bnikmat\b/i',
            '/\bfavorit/i',        // favorit, favorite
            '/\bpopuler\b/i',
            '/\bbest seller\b/i',
            '/\bbestseller\b/i',
            '/\blaris\b/i',
            '/\blaku\b/i',
            '/\bandalan\b/i',
            '/\bsignature\b/i',
            '/\bspesial\b/i',
            '/\bspecial\b/i',
            '/\bpilih/i',          // pilih, pilihan, memilih
            '/\bbingung\b/i',
            
            // Rasa & Kondisi Minuman
            '/\brasa\b/i',
            '/\bmanis\b/i',
            '/\bpahit\b/i',
            '/\basam\b/i',
            '/\basem\b/i',
            '/\bsegar\b/i',
            '/\bseger\b/i',
            '/\bcreamy\b/i',
            '/\bgurih\b/i',
            '/\bpekat\b/i',
            '/\bbold\b/i',
            '/\bdingin\b/i',
            '/\bice\b/i',
            '/\bes\b/i',
            '/\bpanas\b/i',
            '/\bhot\b/i',
            '/\bhangat\b/i',
            '/\bgula\b/i',
            '/\bsugar\b/i',
            '/\bsirup\b/i',
            
            // Bahan & Alergen
            '/\bbahan\b/i',
            '/\bkomposisi\b/i',
            '/\bkandungan\b/i',
            '/\balergi/i',
            '/\balergen\b/i',
            '/\bsusu\b/i',
            '/\bmilk\b/i',
            '/\boat\b/i',
            '/\boatly\b/i',
            '/\balmond\b/i',
            '/\blaktosa\b/i',
            '/\bkacang\b/i',
            '/\bgluten\b/i',
            '/\bvegan\b/i',
            '/\bnabati\b/i',
            '/\bhalal\b/i',
            '/\bkalori\b/i',
            '/\bkafein\b/i',
            
            // Kafe, Identitas & Lokasi
            '/\bfountain\b/i',
            '/\bhide\b/i',
            '/\bkafe\b/i',
            '/\bcafe\b/i',
            '/\bcoffee shop\b/i',
            '/\bkedai\b/i',
            '/\btempat\b/i',
            '/\blokasi\b/i',
            '/\balamat\b/i',
            '/\bdimana\b/i',
            '/\btirta bhakti\b/i',
            '/\btirta\b/i',
            '/\bbhakti\b/i',
            '/\bjakarta\b/i',
            '/\bjam buka\b/i',
            '/\bjam operasional\b/i',
            '/\boperasional\b/i',
            '/\btutup\b/i',
            '/\bwifi\b/i',
            '/\bwi-fi\b/i',
            '/\binternet\b/i',
            '/\bpassword\b/i',
            '/\bcolokan\b/i',
            '/\bstopkontak\b/i',
            '/\bwfc\b/i',
            '/\bkerja\b/i',
            '/\bparkir\b/i',
            
            // Sejarah & Profil
            '/\bprofil\b/i',
            '/\btentang\b/i',
            '/\bsejarah\b/i',
            '/\bdibangun\b/i',
            '/\bdidirikan\b/i',
            '/\bberdiri\b/i',
            '/\basal usul\b/i',
            '/\bfilosofi\b/i',
            '/\bkonsep\b/i',
            '/\bcerita\b/i',
            '/\bowner\b/i',
            '/\bpemilik\b/i',
            '/\bpendiri\b/i',
            
            // Transaksi & Meja
            '/\bpesan/i',          // pesan, memesan, pemesanan
            '/\border/i',
            '/\bmeja\b/i',
            '/\bbayar/i',          // bayar, pembayaran
            '/\bqris\b/i',
            '/\btunai\b/i',
            '/\bcash\b/i',
            '/\bkasir\b/i',
            '/\bstatus\b/i',
            '/\bdapur\b/i',
            '/\bantar/i',
            '/\bstruk\b/i',
            '/\bkeranjang\b/i',
            '/\bharga\b/i',
            '/\bmenu\b/i',
            
            // Greeting & Khusus
            '/\bhalo\b/i',
            '/\bhai\b/i',
            '/\bhello\b/i',
            '/\bpagi\b/i',
            '/\bsiang\b/i',
            '/\bsore\b/i',
            '/\bmalam\b/i',
            '/\bbantuan\b/i',
            '/\btolong\b/i',
            '/\bmanda\b/i',
            '/\bcantik\b/i'
        ];

        foreach ($stemPatterns as $pattern) {
            if (preg_match($pattern, $lower)) {
                return true;
            }
        }

        return false;
    }
}
