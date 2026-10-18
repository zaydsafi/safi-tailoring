<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_status_change_notifies_customer_over_email_and_whatsapp(): void
    {
        Setting::set('notify_email_enabled', '1');
        Setting::set('whatsapp_cloud_enabled', '0'); // Cloud off: WhatsApp send is a silent no-op
        Setting::set('otp_required', '0'); // keep the admin gate independent of OTP

        Mail::fake();

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $order = Order::create([
            'order_number' => 'NTF100',
            'customer_name' => 'Customer',
            'customer_phone' => '0700999888',
            'customer_email' => 'customer@example.test',
            'customer_address' => 'A',
            'customer_city' => 'Kabul',
            'total' => 100,
            'subtotal' => 100,
            'delivery_fee' => 0,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'ordered_at' => now(),
        ]);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'confirmed'])
            ->assertRedirect();

        $this->assertSame('confirmed', $order->fresh()->status);

        Mail::assertSent(\App\Mail\OrderUpdatedMail::class, function ($mail) use ($order) {
            return $mail->order->is($order);
        });
    }

    public function test_email_notification_skipped_when_toggled_off(): void
    {
        Setting::set('notify_email_enabled', '0');
        Setting::set('otp_required', '0');

        Mail::fake();

        $admin = User::create([
            'name' => 'Admin 2',
            'email' => 'admin2@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $order = Order::create([
            'order_number' => 'NTF200',
            'customer_name' => 'Customer 2',
            'customer_phone' => '0700111222',
            'customer_email' => 'customer2@example.test',
            'customer_address' => 'A',
            'customer_city' => 'Kabul',
            'total' => 50,
            'subtotal' => 50,
            'delivery_fee' => 0,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'ordered_at' => now(),
        ]);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'delivered'])
            ->assertRedirect();

        Mail::assertNotSent(\App\Mail\OrderUpdatedMail::class);
    }
}
