<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\Table;
use App\Models\User;
use App\Services\AiAssistantService;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantSystemTest extends TestCase
{
    public function test_customer_menu_can_be_viewed_with_table_identity()
    {
        $response = $this->get('/?meja=05');
        $response->assertStatus(200);
        $response->assertSee('Meja 05');
        $response->assertSee('Ayam Sambal Matah');
    }

    public function test_customer_can_view_product_detail()
    {
        $response = $this->get('/menu/ayam-sambal-matah');
        $response->assertStatus(200);
        $response->assertSee('Ayam Sambal Matah');
        $response->assertSee('Pilih nasi');
        $response->assertSee('Sambalnya gimana?');
    }

    public function test_customer_can_add_to_cart_and_checkout_with_qris()
    {
        $product = Product::where('slug', 'ayam-sambal-matah')->first();

        // 1. Add to cart
        $cartData = [
            'item-test-qris' => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 2,
                'options' => [],
            ]
        ];

        // 2. Checkout view
        $checkoutView = $this->withSession(['cart' => $cartData, 'current_table' => 'Meja 05'])->get('/checkout');
        $checkoutView->assertStatus(200);
        $checkoutView->assertSee('QRIS Dinamis');

        // 3. Process Checkout with QRIS
        $payResponse = $this->withSession(['cart' => $cartData, 'current_table' => 'Meja 05'])->post('/checkout/pay', [
            'customer_name' => 'Budi Santoso',
            'payment_method' => 'qris',
            'table_number' => 'Meja 05',
        ]);
        $payResponse->assertRedirect();

        // Verify order created in database
        $order = Order::where('customer_name', 'Budi Santoso')->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('qris', $order->payment_method);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals('waiting_payment', $order->status);

        // 4. Test Sandbox QRIS Simulation
        $simResponse = $this->post('/payment/qris/simulate/' . urlencode($order->order_number));
        $simResponse->assertRedirect();

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('received', $order->status); // Advanced to Stage 3
    }

    public function test_customer_can_checkout_with_cash_and_cashier_confirms()
    {
        $product = Product::where('slug', 'es-teh-leci')->first();

        $cartData = [
            'item-test-cash' => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'options' => [],
            ]
        ];

        // 2. Checkout with Cash
        $payResponse = $this->withSession(['cart' => $cartData, 'current_table' => 'Meja 02'])->post('/checkout/pay', [
            'customer_name' => 'Siti Rahma',
            'payment_method' => 'cash',
            'table_number' => 'Meja 02',
        ]);

        $order = Order::where('customer_name', 'Siti Rahma')->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('cash', $order->payment_method);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals('waiting_payment', $order->status);

        // 3. Cashier login and confirms cash
        $cashier = User::where('role', 'cashier')->first();
        $this->actingAs($cashier);

        $confirmResponse = $this->post('/kasir/orders/' . $order->id . '/confirm-cash');
        $confirmResponse->assertRedirect();

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('received', $order->status);
        $this->assertEquals($cashier->id, $order->confirmed_by);

        // 4. Cashier advances stages: cooking -> preparing -> delivering -> completed
        $this->post('/kasir/orders/' . $order->id . '/status', ['status' => 'cooking']);
        $this->assertEquals('cooking', $order->fresh()->status);

        $this->post('/kasir/orders/' . $order->id . '/status', ['status' => 'preparing']);
        $this->assertEquals('preparing', $order->fresh()->status);

        $this->post('/kasir/orders/' . $order->id . '/status', ['status' => 'delivering']);
        $this->assertEquals('delivering', $order->fresh()->status);

        $this->post('/kasir/orders/' . $order->id . '/status', ['status' => 'completed']);
        $this->assertEquals('completed', $order->fresh()->status);
    }

    public function test_ai_assistant_scope_restriction()
    {
        $service = app(AiAssistantService::class);

        // Valid restaurant questions
        $ans1 = $service->reply('apakah jamur crispy mengandung susu dan kacang?');
        $this->assertNotEquals('Pertanyaan tidak sesuai.', $ans1['text']);
        $this->assertStringContainsString('Jamur Crispy', $ans1['text']);

        $ans2 = $service->reply('dimana lokasi dan alamat restoran suap?');
        $this->assertNotEquals('Pertanyaan tidak sesuai.', $ans2['text']);
        $this->assertStringContainsString('Cikini', $ans2['text']);

        $ans3 = $service->reply('jam buka restoran sampai jam berapa?');
        $this->assertNotEquals('Pertanyaan tidak sesuai.', $ans3['text']);
        $this->assertMatchesRegularExpression('/(jam|buka|operasional|wib)/i', $ans3['text']);

        // Out-of-scope questions MUST return "Pertanyaan tidak sesuai."
        $off1 = $service->reply('siapa presiden amerika serikat?');
        $this->assertEquals('Pertanyaan tidak sesuai.', $off1['text']);

        $off2 = $service->reply('cara memperbaiki mesin mobil mogok');
        $this->assertEquals('Pertanyaan tidak sesuai.', $off2['text']);

        $off3 = $service->reply('berapa hasil 125 kali 84?');
        $this->assertEquals('Pertanyaan tidak sesuai.', $off3['text']);
    }

    public function test_cashier_access_security()
    {
        // Unauthenticated customer cannot access /kasir
        $response = $this->get('/kasir');
        $response->assertRedirect('/kasir/login');

        // Login as Cashier works
        $cashier = User::where('role', 'cashier')->first();
        $this->actingAs($cashier);

        $dash = $this->get('/kasir');
        $dash->assertStatus(200);
        $dash->assertSee('Kasir SUAP.');

        // Cashier cannot access /admin (redirected to admin login with alert)
        $adminAccess = $this->get('/admin');
        $adminAccess->assertRedirect('/admin/login');
    }

    public function test_admin_dashboard_and_reports()
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        // Admin Dashboard
        $dash = $this->get('/admin');
        $dash->assertStatus(200);
        $dash->assertSee('Ringkasan Operasional Restoran');

        // Admin Reports
        $rep = $this->get('/admin/reports');
        $rep->assertStatus(200);
        $rep->assertSee('Laporan Keuangan Restoran');

        // Admin Menu Management
        $menu = $this->get('/admin/menu');
        $menu->assertStatus(200);
        $menu->assertSee('Manajemen Menu');
    }

    public function test_customer_cannot_order_out_of_stock_product()
    {
        $product = Product::where('slug', 'es-teh-leci')->first();
        $product->update(['stock' => 0, 'daily_stock' => 50, 'track_stock' => true]);

        // Customer attempts to add out-of-stock item
        $response = $this->post('/cart/add', [
            'dish_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHas('error');
        $this->assertTrue($product->fresh()->isOutOfStock());

        // Restore stock
        $product->update(['stock' => 50]);
    }

    public function test_checkout_decrements_product_stock()
    {
        $product = Product::where('slug', 'mie-ayam-suap')->first();
        $initialStock = 25;
        $product->update(['stock' => $initialStock, 'daily_stock' => 50, 'track_stock' => true]);

        $qtyToOrder = 3;
        $cartData = [
            'item-test-stock' => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $qtyToOrder,
                'options' => [],
            ]
        ];

        $payResponse = $this->withSession(['cart' => $cartData, 'current_table' => 'Meja 03'])->post('/checkout/pay', [
            'customer_name' => 'Doni Stock Test',
            'payment_method' => 'cash',
            'table_number' => 'Meja 03',
        ]);
        $payResponse->assertRedirect();

        $this->assertEquals($initialStock - $qtyToOrder, $product->fresh()->stock);
    }

    public function test_admin_can_update_stock_and_reset_daily_stock()
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $product = Product::where('slug', 'jamur-crispy')->first();
        $product->update(['daily_stock' => 40, 'stock' => 40, 'track_stock' => true]);

        // 1. Admin updates stock to 5
        $updateResp = $this->post('/admin/menu/' . $product->id . '/stock', [
            'stock' => 5,
        ]);
        $updateResp->assertRedirect();
        $this->assertEquals(5, $product->fresh()->stock);

        // 2. Admin resets daily stock
        $resetResp = $this->post('/admin/menu/reset-daily-stock');
        $resetResp->assertRedirect();
        $this->assertEquals(40, $product->fresh()->stock);
    }
}
