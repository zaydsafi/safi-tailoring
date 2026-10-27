<?php

namespace Tests\Feature;

use App\Mail\BackupMail;
use App\Models\Category;
use App\Models\EmailDelivery;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\Backup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BackupFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'name' => 'Backup Admin',
            'email' => 'backup-admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $category = Category::create(['name' => 'Suits', 'slug' => 'suits']);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Page Coat',
            'slug' => 'page-coat',
            'type' => 'ready',
            'price' => 2500,
            'stock' => 3,
        ]);
    }

    public function test_backup_contains_data_and_restore_recovers_it(): void
    {
        $name = Backup::create();

        $this->assertTrue(Backup::exists($name));

        // The dump contains the seeded rows.
        $dump = (string) file_get_contents(Backup::path($name));
        $this->assertStringContainsString('Page Coat', $dump);
        $this->assertStringContainsString('backup-admin@example.test', $dump);

        // Mutate after the backup: lose one product, gain another.
        Product::query()->where('slug', 'page-coat')->delete();
        Product::create([
            'category_id' => Category::first()->id,
            'name' => 'Scarf',
            'slug' => 'scarf',
            'type' => 'ready',
            'price' => 300,
            'stock' => 1,
        ]);

        $this->assertDatabaseMissing('products', ['slug' => 'page-coat']);
        $this->assertDatabaseHas('products', ['slug' => 'scarf']);

        Backup::restore($name);

        // Restore puts the pre-mutation state back, byte for byte.
        $this->assertDatabaseHas('products', ['slug' => 'page-coat']);
        $this->assertDatabaseMissing('products', ['slug' => 'scarf']);
    }

    public function test_restore_rejects_unsafe_or_missing_names(): void
    {
        $this->expectException(\RuntimeException::class);

        Backup::restore('../.env');
    }

    public function test_dump_tables_reports_every_table_and_its_row_count(): void
    {
        $tables = Backup::dumpTables(Backup::create());

        $this->assertGreaterThan(0, $tables['users'] ?? 0, 'The dump rows should be counted per table.');
        $this->assertSame(1, $tables['products'] ?? 0);
    }

    public function test_partial_restore_recovers_only_the_selected_tables(): void
    {
        $name = Backup::create();

        // Mutate two tables after the backup: products loses its row and
        // gains another; users gains one extra account.
        Product::query()->where('slug', 'page-coat')->delete();
        Product::create([
            'category_id' => Category::first()->id,
            'name' => 'Scarf',
            'slug' => 'scarf',
            'type' => 'ready',
            'price' => 300,
            'stock' => 1,
        ]);
        User::create([
            'name' => 'Extra Customer',
            'email' => 'extra@example.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        Backup::restore($name, ['products']);

        // Only the products table went back in time.
        $this->assertDatabaseHas('products', ['slug' => 'page-coat']);
        $this->assertDatabaseMissing('products', ['slug' => 'scarf']);
        $this->assertDatabaseHas('users', ['email' => 'extra@example.test']);
    }

    public function test_partial_restore_rejects_tables_the_dump_lacks(): void
    {
        $this->expectException(\RuntimeException::class);

        Backup::restore(Backup::create(), ['not_in_the_dump']);
    }

    public function test_artisan_command_creates_backup_and_prunes(): void
    {
        // Distinct timestamps so the three backup names and their order
        // are deterministic.
        Carbon::setTestNow('2026-10-27 10:00:00');
        Mail::fake();
        $first = Backup::create();

        Carbon::setTestNow('2026-10-27 10:05:00');
        $second = Backup::create();

        Carbon::setTestNow('2026-10-27 10:10:00');
        $this->artisan('shop:backup', ['--prune' => 1])->expectsOutputToContain('Backup created')->run();

        Carbon::setTestNow();

        $files = array_column(Backup::files(), 'name');
        $this->assertCount(1, $files, 'Prune should have kept the newest backup only.');
        $this->assertNotContains($first, $files);
        $this->assertNotContains($second, $files);

        // The scheduled command mails its copy too — exactly one email.
        Mail::assertSent(BackupMail::class, 1);
    }

    public function test_admin_sees_the_backups_page_and_actions(): void
    {
        $admin = User::where('email', 'backup-admin@example.test')->first();

        Backup::create();

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('shop-backup-', false);

        $name = array_column(Backup::files(), 'name')[0];
        $this->assertMatchesRegularExpression(Backup::FILENAME_PATTERN, $name);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->get(route('admin.backups.download', $name))
            ->assertOk()
            ->assertHeader('content-type', 'application/octet-stream');
    }

    public function test_creating_a_backup_emails_a_copy_to_the_shop_owner(): void
    {
        $admin = User::where('email', 'backup-admin@example.test')->first();
        Mail::fake();

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->post(route('admin.backups.store'))
            ->assertRedirect();

        Mail::assertSent(BackupMail::class, 1);
        $this->assertDatabaseHas('email_deliveries', [
            'mailable_class' => BackupMail::class,
            'status' => EmailDelivery::STATUS_SENT,
        ]);
    }

    public function test_no_email_copy_is_sent_without_a_shop_email(): void
    {
        $admin = User::where('email', 'backup-admin@example.test')->first();
        Setting::set('shop_email', '');
        Mail::fake();

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->post(route('admin.backups.store'))
            ->assertRedirect();

        Mail::assertNothingSent();
        $this->assertMatchesRegularExpression(Backup::FILENAME_PATTERN, array_column(Backup::files(), 'name')[0]);
    }

    public function test_backup_download_rejects_unknown_files(): void
    {
        $admin = User::where('email', 'backup-admin@example.test')->first();

        $this->actingAs($admin)
            ->get(route('admin.backups.download', 'not-a-real-backup.sql'))
            ->assertNotFound();
    }

    public function test_recover_page_lists_dump_tables_and_selective_restore_runs(): void
    {
        $admin = User::where('email', 'backup-admin@example.test')->first();
        $name = Backup::create();

        Product::query()->where('slug', 'page-coat')->delete();

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->get(route('admin.backups.recover', $name))
            ->assertOk()
            ->assertSee('products', false)
            ->assertSee('</form>', false);

        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->post(route('admin.backups.restore', $name), ['tables' => ['products']])
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['slug' => 'page-coat']);

        // An unknown table name fails validation rather than touching data.
        $this->withSession(['admin_locale' => 'en'])
            ->actingAs($admin)
            ->from(route('admin.backups.recover', $name))
            ->post(route('admin.backups.restore', $name), ['tables' => ['made_up_table']])
            ->assertRedirect(route('admin.backups.recover', $name))
            ->assertSessionHasErrors('tables.0');
    }
}
