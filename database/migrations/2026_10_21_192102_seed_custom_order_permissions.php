<?php

use App\Models\Permission;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;

/**
 * The custom-order permissions were added to Permissions::CATALOG after the
 * roles-and-permissions migration had already run on existing installs, so
 * the keys never landed in the database. This idempotent seed back-fills
 * any missing catalog keys and grants the tailoring role its two default ones.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Permissions::CATALOG as $key => $label) {
            Permission::firstOrCreate(['key' => $key], ['label' => $label]);
        }

        $tailor = Role::firstOrCreate(
            ['slug' => 'tailor'],
            ['name' => 'Tailor', 'description' => 'Tailoring team: custom orders, measurements, and order status for in-tailoring work.']
        );

        $tailor->permissions()->syncWithoutDetaching(
            Permission::query()->whereIn('key', [
                'custom_orders',
                'tailor_dashboard',
            ])->pluck('id')->all()
        );
    }

    public function down(): void
    {
        $tailor = Role::query()->where('slug', 'tailor')->first();

        $tailor?->permissions()->detach(
            Permission::query()->whereIn('key', ['custom_orders', 'tailor_dashboard'])->pluck('id')->all()
        );
    }
};
