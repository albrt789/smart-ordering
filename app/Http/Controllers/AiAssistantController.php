<?php

namespace App\Http\Controllers;

use App\Models\AiMessage;
use App\Models\Product;
use App\Services\AiAssistantService;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    protected AiAssistantService $aiService;

    public function __construct(AiAssistantService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function chat(Request $request)
    {
        $table = session('current_table', 'Meja 08');
        if ($request->has('meja')) {
            $t = $request->get('meja');
            $table = str_starts_with($t, 'Meja') ? $t : 'Meja ' . str_pad($t, 2, '0', STR_PAD_LEFT);
            session(['current_table' => $table]);
        }

        $messages = AiMessage::where('table_number', $table)->with('product')->oldest()->get();
        if ($messages->isEmpty()) {
            $messages = AiMessage::with('product')->oldest()->get();
        }

        $cart = session('cart', []);
        $cartCount = array_sum(array_column($cart, 'quantity'));
        $subtotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));

        return view('ai.chat', compact('table', 'messages', 'cartCount', 'subtotal'));
    }

    public function sendMessage(Request $request)
    {
        $table = session('current_table', 'Meja 08');
        $text = trim($request->input('message', ''));

        if (!$text) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Pesan tidak boleh kosong.'], 422);
            }
            return redirect()->route('ai.chat');
        }

        $now = now()->format('H.i');

        // 1. Simpan pesan user
        $userMsg = AiMessage::create([
            'table_number' => $table,
            'sender' => 'user',
            'text' => $text,
            'time' => $now,
            'has_allergen_warning' => false,
            'product_id' => null,
            'show_cart_ready_card' => false,
        ]);

        // 2. Evaluasi menggunakan AiAssistantService (dengan Strict Scope Restriction)
        $aiResponse = $this->aiService->reply($text, $table);

        // 3. Simpan jawaban AI
        $aiMsg = AiMessage::create([
            'table_number' => $table,
            'sender' => 'ai',
            'text' => $aiResponse['text'],
            'time' => $now,
            'has_allergen_warning' => $aiResponse['has_allergen_warning'] ?? false,
            'product_id' => $aiResponse['product_id'] ?? null,
            'show_cart_ready_card' => $aiResponse['show_cart_ready_card'] ?? false,
        ]);

        if ($request->wantsJson()) {
            $product = null;
            if ($aiMsg->product_id) {
                $product = Product::find($aiMsg->product_id);
            }
            return response()->json([
                'success' => true,
                'user_message' => $userMsg,
                'ai_message' => $aiMsg,
                'product' => $product,
            ]);
        }

        return redirect()->route('ai.chat');
    }
}
