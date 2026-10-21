<?php

namespace Tests\Feature;

use App\Mail\CustomOrderStatusMail;
use App\Models\CustomOrderRequest;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_custom_order_request_and_reach_thankyou(): void
    {
        Setting::set('otp_required', '0');
        Setting::set('notify_whatsapp_enabled', '0');

        // The storefront request page itself renders, including the nav link.
        $this->withSession(['locale' => 'en'])
            ->get(route('custom-order.request', 'en'))
            ->assertOk()
            ->assertSee('Contact Details', false);

        // The storefront nav exposes the custom-order entry point.
        $this->withSession(['locale' => 'en'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('/en/custom-order"', false);

        $this->withSession(['locale' => 'en'])
            ->post(route('custom-order.request.store', 'en'), [
                'customer_name' => 'Walk-in Warmi',
                'customer_phone' => '0700111222',
                'customer_address' => 'Street 4, Kabul',
                'request_description' => 'Two-piece kameez with embroidery',
                'measurements' => ['chest' => '40', 'waist' => '34.5', 'neck' => ''],
            ])
            ->assertRedirect();

        $requestRecord = CustomOrderRequest::where('customer_phone', '0700111222')->firstOrFail();

        $this->assertSame('new', $requestRecord->status);
        $this->assertNull($requestRecord->user_id);
        // JSON round-trips 40.0 as int 40; compare loosely.
        $this->assertEquals(['chest' => 40, 'waist' => 34.5], $requestRecord->measurements);

        // The thank-you page stays reachable for guests.
        $this->withSession(['locale' => 'en'])
            ->get(route('custom-order.request.thankyou', ['locale' => 'en', 'requestRecord' => $requestRecord]))
            ->assertOk()
            ->assertSee('#'.$requestRecord->id, false);
    }

    public function test_admin_can_update_status_and_customer_gets_email(): void
    {
        Setting::set('notify_email_enabled', '1');
        Setting::set('otp_required', '0');

        Mail::fake();

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $requestRecord = CustomOrderRequest::create([
            'customer_name' => 'Email Warmi',
            'customer_phone' => '0700333444',
            'customer_email' => 'warmi@example.test',
            'customer_address' => 'Kabul',
            'request_description' => 'Wedding dress',
        ]);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->patch(route('admin.custom-orders.status', $requestRecord), [
                'status' => 'in_progress',
                'assigned_to' => 'Zahra',
            ])
            ->assertRedirect();

        $requestRecord->refresh();

        $this->assertSame('in_progress', $requestRecord->status);
        $this->assertSame('Zahra', $requestRecord->assigned_to);

        Mail::assertSent(CustomOrderStatusMail::class, fn ($mail) => $mail->request->is($requestRecord));
    }

    public function test_admin_can_convert_request_into_order(): void
    {
        Setting::set('otp_required', '0');

        $customer = User::create([
            'name' => 'Registered Warmi',
            'email' => 'registered@example.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'phone' => '0700555666',
            'email_verified_at' => now(),
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $requestRecord = CustomOrderRequest::create([
            'user_id' => $customer->id,
            'customer_name' => 'Registered Warmi',
            'customer_phone' => '0700555666',
            'customer_email' => 'registered@example.test',
            'customer_address' => 'Street 9, Kabul',
            'customer_city' => 'Kabul',
            'request_description' => 'Two-piece suit',
            'measurements' => ['chest' => 40, 'waist' => 34],
        ]);

        Mail::fake();

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->post(route('admin.custom-orders.convert', $requestRecord), [
                'items' => [
                    [
                        'description' => 'Two-piece suit, charcoal',
                        'measurements' => ['chest' => '40', 'waist' => '34', 'sleeve' => ''],
                        'price' => '4500.50',
                        'quantity' => '2',
                    ],
                ],
                'notes' => 'Deliver before Eid',
            ])
            ->assertRedirect();

        $order = Order::where('customer_phone', '0700555666')->firstOrFail();

        $this->assertSame(9001.0, (float) $order->subtotal);
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame(1, $order->items->count());
        $this->assertSame('custom', $order->items->first()->type);
        $this->assertEquals(['chest' => 40, 'waist' => 34], $order->items->first()->measurements);

        $requestRecord->refresh();

        $this->assertSame('converted', $requestRecord->status);
        $this->assertSame($order->id, $requestRecord->converted_order_id);
        $this->assertNotNull($requestRecord->converted_at);
    }

    public function test_admin_custom_order_pages_render(): void
    {
        Setting::set('otp_required', '0');

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $requestRecord = CustomOrderRequest::create([
            'customer_name' => 'Page Warmi',
            'customer_phone' => '0700888999',
            'customer_address' => 'Kabul',
            'request_description' => 'Sherwani',
            'measurements' => ['chest' => 42],
        ]);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->get(route('admin.custom-orders.index'))
            ->assertOk()
            ->assertSee('Page Warmi', false);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->get(route('admin.custom-orders.show', $requestRecord))
            ->assertOk()
            ->assertSee('Sherwani', false)
            ->assertSee('Chest', false);
    }

    public function test_staff_can_create_order_for_selected_customer(): void
    {
        Setting::set('otp_required', '0');

        $customer = User::create([
            'name' => 'In-store Buyer',
            'email' => 'instore@example.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'phone' => '0700777888',
            'email_verified_at' => now(),
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        Mail::fake();

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->get(route('admin.custom-orders.create', ['customer_id' => $customer->id]))
            ->assertOk()
            ->assertSee('In-store Buyer', false)
            ->assertSee('In-store Buyer — 0700777888', false);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->post(route('admin.custom-orders.store'), [
                'customer_id' => $customer->id,
                'customer_name' => 'In-store Buyer',
                'customer_phone' => '0700777888',
                'customer_address' => 'Shop 3, Kabul',
                'items' => [
                    ['description' => 'Sherwani', 'price' => '9500', 'quantity' => '1'],
                ],
            ])
            ->assertRedirect();

        $order = Order::where('customer_phone', '0700777888')->firstOrFail();

        $this->assertSame(9500.0, (float) $order->subtotal);
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame('Sherwani', $order->items->first()->product_name);
    }
}
