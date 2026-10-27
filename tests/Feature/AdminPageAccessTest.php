<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\CustomOrderRequest;
use App\Models\EmailDelivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Translation;
use App\Models\User;
use App\Support\Backup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AdminPageAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $tailor;

    private Role $tailorRole;

    private User $customer;

    private Category $category;

    private Product $product;

    private Order $order;

    private CustomOrderRequest $requestRecord;

    private ContactMessage $message;

    private EmailDelivery $emailDelivery;

    private Translation $translation;

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

        // Mirror the production tailor setup: a staff role carrying only
        // tailor_dashboard and custom_orders, plus a user whose role
        // string points at the role's slug. The migrations already seed
        // the role and permission rows, so find them.
        $this->tailorRole = Role::firstWhere('slug', 'tailor')
            ?? Role::create(['name' => 'Tailor', 'slug' => 'tailor']);
        $this->tailorRole->permissions()->sync(
            Permission::query()->whereIn('key', ['tailor_dashboard', 'custom_orders'])->pluck('id')
        );

        $this->tailor = User::create([
            'name' => 'Tailor',
            'email' => 'tailor@example.test',
            'password' => bcrypt('password'),
            'role' => $this->tailorRole->slug,
            'email_verified_at' => now(),
        ]);

        $this->customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@example.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->category = Category::create([
            'name' => 'Suits',
            'slug' => 'suits',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Page Coat',
            'slug' => 'page-coat',
            'type' => 'ready',
            'price' => 2500,
            'stock' => 3,
        ]);

        $this->order = Order::create([
            'order_number' => 'PAGE100',
            'user_id' => $this->customer->id,
            'customer_name' => 'Customer Name',
            'customer_phone' => '0700111222',
            'customer_address' => 'Street 1',
            'subtotal' => 2500,
            'delivery_fee' => 0,
            'total' => 2500,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'status' => 'pending',
            'ordered_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_name' => 'Page Coat',
            'type' => 'ready',
            'size' => 'M',
            'price' => 2500,
            'quantity' => 1,
            'line_total' => 2500,
        ]);

        $this->requestRecord = CustomOrderRequest::create([
            'customer_name' => 'Page Warmi',
            'customer_phone' => '0700222333',
            'customer_address' => 'Kabul',
            'request_description' => 'Sherwani',
        ]);

        $this->message = ContactMessage::create([
            'name' => 'Page Contact',
            'email' => 'contact@example.test',
            'phone' => '0700333444',
            'message' => 'Do you stitch sherwanis?',
        ]);

        $this->emailDelivery = EmailDelivery::create([
            'subject' => 'Order placed',
            'recipient' => 'customer@example.test',
            'mailable_class' => self::class,
            'status' => 'sent',
        ]);

        $this->translation = Translation::create([
            'group' => 'test',
            'key' => 'test.page_access',
            'en' => 'Page access',
        ]);
    }

    /**
     * Every admin page as the URL the request should hit. Parameterized
     * routes point at records created in setUp(). admin.login is
     * guest-only and admin.lang is a locale redirect helper, so neither
     * is a normal page and both are excluded.
     *
     * @return array<string, string>
     */
    private function pages(): array
    {
        return [
            'dashboard' => route('admin.dashboard'),
            'roles.index' => route('admin.roles.index'),
            'roles.create' => route('admin.roles.create'),
            'roles.edit' => route('admin.roles.edit', $this->tailorRole),
            'custom-orders.index' => route('admin.custom-orders.index'),
            'custom-orders.create' => route('admin.custom-orders.create'),
            'custom-orders.show' => route('admin.custom-orders.show', $this->requestRecord),
            'tailor.dashboard' => route('admin.tailor.dashboard'),
            'tailor.requests' => route('admin.tailor.requests'),
            'categories.index' => route('admin.categories.index'),
            'categories.create' => route('admin.categories.create'),
            'categories.edit' => route('admin.categories.edit', $this->category),
            'products.index' => route('admin.products.index'),
            'products.create' => route('admin.products.create'),
            'products.edit' => route('admin.products.edit', $this->product),
            'exports.orders' => route('admin.exports.orders'),
            'exports.sales_summary' => route('admin.exports.sales_summary'),
            'exports.products' => route('admin.exports.products'),
            'exports.customers' => route('admin.exports.customers'),
            'exports.custom_orders' => route('admin.exports.custom_orders'),
            'exports.messages' => route('admin.exports.messages'),
            'exports.email_log' => route('admin.exports.email_log'),
            'orders.index' => route('admin.orders.index'),
            'orders.show' => route('admin.orders.show', $this->order),
            'orders.invoice' => route('admin.orders.invoice', $this->order),
            'users.index' => route('admin.users.index'),
            'users.create' => route('admin.users.create'),
            'users.edit' => route('admin.users.edit', $this->customer),
            'customers.show' => route('admin.customers.show', $this->customer),
            'translations.index' => route('admin.translations.index'),
            'translations.missing' => route('admin.translations.missing'),
            'translations.create' => route('admin.translations.create'),
            'translations.edit' => route('admin.translations.edit', $this->translation),
            'messages.index' => route('admin.messages.index'),
            'messages.show' => route('admin.messages.show', $this->message),
            'email-log.index' => route('admin.email-log.index'),
            'backups.index' => route('admin.backups.index'),
            'backups.recover' => route('admin.backups.recover', basename(Backup::create())),
            'settings.edit' => route('admin.settings.edit'),
        ];
    }

    /**
     * Pages the tailor's permission set covers. The tailor role is
     * limited to tailor_dashboard + custom_orders, and its exports touch
     * only those, while the dashboard itself carries no permission gate.
     *
     * @return list<string>
     */
    private function tailorPages(): array
    {
        return [
            'dashboard',
            'custom-orders.index',
            'custom-orders.create',
            'custom-orders.show',
            'exports.custom_orders',
            'tailor.dashboard',
            'tailor.requests',
        ];
    }

    public function test_every_admin_page_returns_200_for_admin(): void
    {
        foreach ($this->pages() as $name => $url) {
            $this->withSession(['admin_locale' => 'en'])
                ->actingAs($this->admin)
                ->get($url)
                ->assertStatus(200, "Admin failed on page: {$name}");
        }

        // The parameterized pages really rendered their row.
        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($this->admin)
            ->get(route('admin.orders.show', $this->order))
            ->assertSee($this->order->order_number, false)
            ->assertSee('Page Coat', false);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($this->admin)
            ->get(route('admin.custom-orders.show', $this->requestRecord))
            ->assertSee('Sherwani', false);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($this->admin)
            ->get(route('admin.messages.show', $this->message))
            ->assertSee('sherwanis', false);
    }

    public function test_tailor_gets_200_only_on_permitted_pages(): void
    {
        $pages = $this->pages();

        foreach ($this->tailorPages() as $name) {
            $this->withSession(['admin_locale' => 'en'])
                ->actingAs($this->tailor)
                ->get($pages[$name])
                ->assertStatus(200, "Tailor should access: {$name}");
        }
    }

    public function test_tailor_is_forbidden_on_all_other_admin_pages(): void
    {
        $forbidden = array_diff(
            array_keys($this->pages()),
            $this->tailorPages()
        );

        $pages = $this->pages();
        $leaked = [];

        foreach ($forbidden as $name) {
            $status = $this->withSession(['admin_locale' => 'en'])
                ->actingAs($this->tailor)
                ->get($pages[$name])
                ->getStatusCode();

            if ($status !== 403) {
                $leaked[] = "{$name} ({$status})";
            }
        }

        $this->assertSame([], $leaked, 'Tailor can open these admin pages: '.implode(', ', $leaked));
    }

    /**
     * Every admin write action (POST / PATCH / PUT / DELETE), with the
     * minimum payload each controller needs. The routes are reached with
     * their HTTP verb middleware handles before the controller runs, so
     * the assertions hold regardless of validation outcomes inside the
     * action itself. admin.login.store is guest-only and admin.logout
     * simply ends the session — neither is an admin feature, so both are
     * excluded.
     *
     * @return array<string, array{0: string, 1: string, 2: array<string, mixed>}>
     */
    private function actions(): array
    {
        // One real dump shared by the create/download/delete actions: the
        // restore replays it and the destroy then removes it.
        $backupFile = basename(Backup::create());

        return [
            'roles.store' => ['post', route('admin.roles.store'), [
                'name' => 'Labeler', 'description' => 'Adds labels to garments.',
            ]],
            'roles.update' => ['put', route('admin.roles.update', $this->tailorRole), [
                'name' => 'Tailor', 'description' => 'Handles the stitching queue.',
            ]],
            'roles.destroy' => ['delete', route('admin.roles.destroy', $this->anotherStaffRole()), []],
            'custom-orders.store' => ['post', route('admin.custom-orders.store'), [
                'customer_id' => $this->customer->id,
                'customer_name' => 'Customer',
                'customer_phone' => '0700111222',
                'customer_address' => 'Street 1',
                'items' => [
                    ['description' => 'Kameez', 'price' => '1200', 'quantity' => '1'],
                ],
            ]],
            'custom-orders.status' => ['patch', route('admin.custom-orders.status', $this->requestRecord), [
                'status' => 'in_progress',
            ]],
            'custom-orders.convert' => ['post', route('admin.custom-orders.convert', $this->requestRecord), [
                'items' => [
                    ['description' => 'Sherwani', 'measurements' => ['chest' => '42'], 'price' => '7500', 'quantity' => '1'],
                ],
            ]],
            'orders.status' => ['patch', route('admin.orders.status', $this->order), [
                'status' => 'confirmed',
            ]],
            'orders.payment' => ['patch', route('admin.orders.payment', $this->order), [
                'payment_status' => 'paid',
            ]],
            'orders.destroy' => ['delete', route('admin.orders.destroy', $this->order), []],
            'users.store' => ['post', route('admin.users.store'), [
                'name' => 'New Staff', 'email' => 'staff@example.test',
                'password' => 'secret12345', 'role' => 'customer',
            ]],
            'users.update' => ['put', route('admin.users.update', $this->customer), [
                'name' => 'Customer', 'email' => 'customer@example.test',
            ]],
            'users.block' => ['patch', route('admin.users.block', $this->customer), [
                'reason' => 'E2E test',
            ]],
            'users.unblock' => ['patch', route('admin.users.unblock', $this->customer), []],
            'categories.store' => ['post', route('admin.categories.store'), [
                'name' => 'Coats', 'slug' => 'coats', 'is_active' => '1',
            ]],
            'categories.update' => ['put', route('admin.categories.update', $this->category), [
                'name' => 'Suits', 'slug' => 'suits', 'is_active' => '1',
            ]],
            'categories.destroy' => ['delete', route('admin.categories.destroy', Category::create([
                'name' => 'Ties', 'slug' => 'ties',
            ])), []],
            'products.store' => ['post', route('admin.products.store'), [
                'name' => 'E2E Coat', 'slug' => 'e2e-coat', 'type' => 'ready',
                'category_id' => $this->category->id, 'price' => '1000', 'stock' => '2',
            ]],
            'products.update' => ['put', route('admin.products.update', $this->product), [
                'name' => 'Page Coat', 'slug' => 'page-coat', 'type' => 'ready',
                'category_id' => $this->category->id, 'price' => '2500', 'stock' => '3',
            ]],
            'products.destroy' => ['delete', route('admin.products.destroy', Product::create([
                'category_id' => $this->category->id,
                'name' => 'Scarf', 'slug' => 'scarf', 'type' => 'ready',
                'price' => 300, 'stock' => 1,
            ])), []],
            'translations.store' => ['post', route('admin.translations.store'), [
                'group' => 'test', 'key' => 'test.new_row', 'en' => 'New row',
            ]],
            'translations.update' => ['put', route('admin.translations.update', $this->translation), [
                'group' => 'test', 'key' => 'test.page_access', 'en' => 'Page access',
            ]],
            'translations.destroy' => ['delete', route('admin.translations.destroy', Translation::create([
                'group' => 'test', 'key' => 'test.temp_row', 'en' => 'Temp',
            ])), []],
            'messages.destroy' => ['delete', route('admin.messages.destroy', $this->message), []],
            'settings.update' => ['put', route('admin.settings.update'), []],
            'settings.test_email' => ['post', route('admin.settings.test_email'), [
                'recipient' => 'owner@example.test',
            ]],
            'backups.store' => ['post', route('admin.backups.store'), []],
            'backups.restore' => ['post', route('admin.backups.restore', $backupFile), []],
            'backups.destroy' => ['delete', route('admin.backups.destroy', $backupFile), []],
        ];
    }

    /**
     * A throwaway staff role so role management actions do not disturb
     * the tailor role under test.
     */
    private function anotherStaffRole(): Role
    {
        return Role::firstOrCreate(['slug' => 'labeler'], ['name' => 'Labeler']);
    }

    /**
     * Write actions the tailor's permission set covers.
     *
     * @return list<string>
     */
    private function tailorActions(): array
    {
        return [
            'custom-orders.store',
            'custom-orders.status',
            'custom-orders.convert',
        ];
    }

    public function test_every_admin_write_action_is_reachable_for_admin(): void
    {
        foreach ($this->actions() as $name => [$verb, $url, $payload]) {
            $response = $this->withSession(['admin_locale' => 'en'])
                ->actingAs($this->admin)
                ->call($verb, $url, $payload);

            // The perm gate passed and the action ran: the controller
            // answers with its redirect (create/update/convert flows).
            $this->assertSame(
                302,
                $response->getStatusCode(),
                "Admin write action failed: {$name}"
            );
        }
    }

    public function test_tailor_is_forbidden_on_admin_only_write_actions(): void
    {
        $allowed = $this->tailorActions();
        $actions = $this->actions();
        $leaked = [];

        foreach (array_diff(array_keys($actions), $allowed) as $name) {
            [$verb, $url, $payload] = $actions[$name];

            $response = $this->withSession(['admin_locale' => 'en'])
                ->actingAs($this->tailor)
                ->call($verb, $url, $payload);

            if ($response->getStatusCode() !== 403) {
                $leaked[] = "{$name} ({$response->getStatusCode()})";
            }
        }

        $this->assertSame([], $leaked, 'Tailor can perform these admin write actions: '.implode(', ', $leaked));
    }

    public function test_customer_is_kept_off_every_admin_write_action(): void
    {
        foreach ($this->actions() as $name => [$verb, $url, $payload]) {
            $response = $this->actingAs($this->customer)
                ->call($verb, $url, $payload);

            $this->assertSame(
                302,
                $response->getStatusCode(),
                "Customer hit admin write action: {$name}"
            );
            $this->assertTrue(
                str_contains($response->headers->get('location') ?: '', '/admin/login'),
                "Customer write action {$name} did not bounce to the admin login"
            );
        }
    }

    public function test_tailor_can_use_the_custom_order_write_actions(): void
    {
        Excel::fake();

        $actions = $this->actions();

        foreach ($this->tailorActions() as $name) {
            [$verb, $url, $payload] = $actions[$name];

            $this->withSession(['admin_locale' => 'en'])
                ->actingAs($this->tailor)
                ->call($verb, $url, $payload)
                ->assertStatus(302, "Tailor should run: {$name}");
        }
    }
}
