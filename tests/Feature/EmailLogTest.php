<?php

namespace Tests\Feature;

use App\Mail\OrderUpdatedMail;
use App\Mail\ShopTestMail;
use App\Models\EmailDelivery;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Support\EmailLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_recent_email_deliveries_and_failures(): void
    {
        Setting::set('otp_required', '0');

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $sent = EmailDelivery::factory()->create([
            'subject' => 'Order 1001 received',
            'recipient' => 'customer@example.test',
        ]);

        $failed = EmailDelivery::factory()->failed()->create([
            'subject' => 'Order 1002 received',
            'recipient' => 'other@example.test',
        ]);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->get(route('admin.email-log.index'))
            ->assertOk()
            ->assertSee('Order 1001 received')
            ->assertSee('Order 1002 received')
            ->assertSee('customer@example.test')
            ->assertSee('other@example.test')
            ->assertSee('SMTP connection refused (111): smtp.example.test:587')
            ->assertSee('1 failed attempts')
            ->assertSee('Sent')
            ->assertSee('Failed');
    }

    public function test_successful_send_is_logged_as_sent(): void
    {
        Setting::set('notify_email_enabled', '1');
        Setting::set('whatsapp_cloud_enabled', '0');
        Setting::set('otp_required', '0');

        Mail::fake();

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $order = Order::create([
            'order_number' => 'LOG100',
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

        $this->assertDatabaseHas('email_deliveries', [
            'recipient' => 'customer@example.test',
            'mailable_class' => OrderUpdatedMail::class,
            'status' => EmailDelivery::STATUS_SENT,
        ]);
    }

    public function test_smtp_failure_is_logged_with_error_message(): void
    {
        Setting::set('notify_email_enabled', '1');
        Setting::set('whatsapp_cloud_enabled', '0');
        Setting::set('otp_required', '0');

        // Mail::fake() makes any to-send a caught failure when we throw;
        // emulate a transport error by asserting via the failed factory
        // path instead: EmailLog marks failures without raising.
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP server is refusing connections'));

        $delivery = EmailLog::send('customer@example.test', new ShopTestMail);

        $this->assertSame(EmailDelivery::STATUS_FAILED, $delivery->status);
        $this->assertSame('SMTP server is refusing connections', $delivery->error);

        $this->assertDatabaseHas('email_deliveries', [
            'recipient' => 'customer@example.test',
            'status' => EmailDelivery::STATUS_FAILED,
        ]);

        // And the admin sees it on the page.
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->get(route('admin.email-log.index'))
            ->assertOk()
            ->assertSee('SMTP server is refusing connections');
    }
}
