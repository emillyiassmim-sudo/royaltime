<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('cpf')->nullable();
            $table->string('cpf_hash', 64)->nullable()->unique();
            $table->string('phone', 30)->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->text('customer_snapshot')->nullable();
            $table->uuid('checkout_token')->nullable()->unique();
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropUnique(['checkout_token']);
            $table->dropColumn(['customer_snapshot', 'checkout_token']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['cpf_hash']);
            $table->dropColumn(['cpf', 'cpf_hash', 'phone']);
        });
    }
};
