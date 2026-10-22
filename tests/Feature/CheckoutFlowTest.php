<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Support\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Only local, off-line channels should run during checkout tests.
        Setting::set('notify_whatsapp_enabled', '0');
        Setting::set('whatsapp_cloud_enabled', '0');
        Setting::set('hesabpay_enabled', '0');

        $category = Category::create([
            'name' => 'Men',
            'slug' => 'men',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Ready-made kameez',
            'slug' => 'ready-made-kameez',
            'type' => 'ready',
            'price' => '1500',
            'stock' => 2,
            'track_stock' => true,
            'is_active' => true,
        ]);
    }

    public function test_placing_an_order_decrements_tracked_stock(): void
    {
        $this->withSession(['locale' => 'en']);

        $this->post(route('cart.add'), ['product_id' => $this->product->id, 'qty' => 1]);
        $this->post(route('cart.add'), ['product_id' => $this->product->id, 'qty' => 1]);

        $this->post(route('checkout.store'), $this->checkoutPayload())
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $order = Order::firstOrFail();

        $this->assertSame(1, $order->items()->count());
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame(0, $this->product->fresh()->stock);
        $this->assertSame('pending', $order->status);
    }

    public function test_stock_decrement_never_oversells(): void
    {
        // Buys the full stock; a second sale must bounce.
        $this->assertTrue($this->product->decrementStock(2));
        $this->assertFalse($this->product->decrementStock(1));
        $this->assertSame(0, $this->product->fresh()->stock);
    }

    public function test_unavailable_stock_rejects_the_checkout(): void
    {
        $this->withSession(['locale' => 'en']);

        $this->post(route('cart.add'), ['product_id' => $this->product->id]);

        // Another checkout consumed the stock between add-to-cart and paying;
        // the checkout is rejected again before any order is written.
        $this->product->decrementStock(2);

        $this->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertSame(0, $this->product->fresh()->stock);

        // The cart must not be wiped on rejection, so the customer can retry
        // once stock is back.
        $this->assertSame(1, app(Cart::class)->count());
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Guest Buyer',
            'customer_phone' => '0744556677',
            'customer_address' => 'Street 5, Kabul',
            'payment_method' => 'cash_on_delivery',
        ], $overrides);
    }
}
