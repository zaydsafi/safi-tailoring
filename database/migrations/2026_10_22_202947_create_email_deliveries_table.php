<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per outgoing email attempt (customer notifications, OTP mail and
     * the admin test message), so the admin panel can show what was sent and
     * diagnose failures.
     */
    public function up(): void
    {
        Schema::create('email_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 500)->nullable();
            $table->string('recipient', 255);
            $table->string('mailable_class', 255)->nullable();
            $table->string('status', 20)->default('sent'); // sent | failed
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_deliveries');
    }
};
