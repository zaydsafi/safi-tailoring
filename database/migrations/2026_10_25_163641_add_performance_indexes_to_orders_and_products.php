<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hot-path indexes: the storefront filters products by is_active on
     * every catalog query, is_featured powers the homepage block, and the
     * admin dashboard/list screens aggregate and filter orders by status,
     * payment_status and created_at.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('is_active', 'products_is_active_index');
            $table->index(['is_featured', 'is_active'], 'products_featured_active_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status', 'orders_status_index');
            $table->index('payment_status', 'orders_payment_status_index');
            $table->index('created_at', 'orders_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_is_active_index');
            $table->dropIndex('products_featured_active_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_payment_status_index');
            $table->dropIndex('orders_created_at_index');
        });
    }
};
