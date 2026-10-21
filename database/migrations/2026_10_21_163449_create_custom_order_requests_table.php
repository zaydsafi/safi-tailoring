<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_order_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('customer_address');
            $table->string('customer_city')->nullable();
            $table->text('request_description')->nullable();
            $table->string('preferred_fabric')->nullable();
            $table->json('measurements')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('new'); // new | assigned | in_progress | converted | closed
            $table->string('assigned_to')->nullable(); // role slug or user id reference
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('customer_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_order_requests');
    }
};
