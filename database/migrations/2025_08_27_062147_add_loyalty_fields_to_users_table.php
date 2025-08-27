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
            $table->timestamp('first_investment_date')->nullable();
            $table->timestamp('last_withdrawal_date')->nullable();
            $table->integer('loyalty_days')->default(0);
            $table->decimal('loyalty_bonus_earned', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_investment_date', 'last_withdrawal_date', 'loyalty_days', 'loyalty_bonus_earned']);
        });
    }
};
