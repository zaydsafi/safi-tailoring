<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\EmailDelivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class InvoiceExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@example.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->order = Order::create([
            'order_number' => 'INV100',
            'user_id' => $this->customer->id,
            'customer_name' => 'Customer Name',
            'customer_phone' => '0700999888',
            'customer_email' => 'customer@example.test',
            'customer_address' => 'Street 1',
            'customer_city' => 'Kabul',
            'subtotal' => 150,
            'delivery_fee' => 10,
            'total' => 160,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'status' => 'pending',
            'ordered_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_name' => 'Kameez Partug',
            'type' => 'custom',
            'size' => null,
            'measurements' => ['chest' => 40],
            'price' => 150,
            'quantity' => 1,
            'line_total' => 150,
        ]);
    }

    public function test_customer_can_download_invoice_pdf_of_own_order(): void
    {
        $this->actingAs($this->customer)
            ->get(route('account.orders.invoice', ['locale' => 'en', 'order' => $this->order]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_customer_cannot_download_invoice_of_another_customers_order(): void
    {
        $other = User::create([
            'name' => 'Other',
            'email' => 'other@example.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($other)
            ->get(route('account.orders.invoice', ['locale' => 'en', 'order' => $this->order]))
            ->assertForbidden();
    }

    public function test_admin_can_download_order_invoice_pdf(): void
    {
        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($this->admin)
            ->get(route('admin.orders.invoice', $this->order))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_guest_receipt_after_checkout_success_and_forbidden_without_session(): void
    {
        $this->get(route('orders.guest_invoice', ['locale' => 'en', 'order' => $this->order]))
            ->assertForbidden();

        $this->withSession(['order_confirmed' => $this->order->order_number])
            ->get(route('orders.guest_invoice', ['locale' => 'en', 'order' => $this->order]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_orders_excel_export_downloads(): void
    {
        Excel::fake();
        Excel::matchByRegex();

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($this->admin)
            ->get(route('admin.exports.orders'))
            ->assertOk();

        Excel::assertDownloaded('/^orders-[\d-]+\.xlsx$/');
    }

    public function test_sales_summary_csv_export_downloads(): void
    {
        Excel::fake();
        Excel::matchByRegex();

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($this->admin)
            ->get(route('admin.exports.sales_summary', ['format' => 'csv']))
            ->assertOk();

        Excel::assertDownloaded('/^sales-summary-[\d-]+\.csv$/');
    }

    public function test_customer_cannot_access_admin_exports(): void
    {
        $this->actingAs($this->customer)
            ->get(route('admin.exports.orders'))
            ->assertStatus(302)
            ->assertRedirect();
    }

    public function test_message_and_email_log_exports_work_for_admin(): void
    {
        ContactMessage::create(['name' => 'Contact', 'email' => 'c@example.test', 'phone' => '070', 'message' => 'Hello', 'is_read' => false]);
        EmailDelivery::create(['subject' => 'Test subject', 'recipient' => 'r@example.test', 'mailable_class' => self::class, 'status' => 'sent']);

        Excel::fake();
        Excel::matchByRegex();

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($this->admin)
            ->get(route('admin.exports.messages'))
            ->assertOk();

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($this->admin)
            ->get(route('admin.exports.email_log'))
            ->assertOk();

        Excel::assertDownloaded('/^contact-messages-[\d-]+\.(xlsx|csv)$/');
        Excel::assertDownloaded('/^email-log-[\d-]+\.(xlsx|csv)$/');
    }

    public function test_real_csv_export_streams_a_download_response(): void
    {
        // Without Excel::fake() the real writer runs end-to-end and
        // streams a forced download (attachment disposition).
        $response = $this->withSession(['admin_locale' => 'en'])
            ->actingAs($this->admin)
            ->get(route('admin.exports.products', ['format' => 'csv']))
            ->assertOk();

        $disposition = (string) $response->headers->get('content-disposition');

        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('products-', $disposition);
        $this->assertStringContainsString('.csv', $disposition);
    }
}
