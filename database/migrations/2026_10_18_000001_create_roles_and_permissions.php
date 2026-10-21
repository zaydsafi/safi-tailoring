<?php

use App\Models\Permission;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('description', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('label', 100);
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        // The built-in roles. The admin role is a super-role: it grants
        // everything and its permissions can never be edited.
        Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin', 'description' => 'Full access to everything.']
        );

        foreach (Permissions::CATALOG as $key => $label) {
            Permission::firstOrCreate(['key' => $key], ['label' => $label]);
        }

        // Built-in staff role for the tailoring team. It is editable like any
        // other custom role; this is only the initial seed so it exists by default.
        Role::firstOrCreate(
            ['slug' => 'tailor'],
            ['name' => 'Tailor', 'description' => 'Tailoring team: custom orders, measurements, and order status for in-tailoring work.']
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
