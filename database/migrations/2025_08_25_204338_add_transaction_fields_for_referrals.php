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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('type')->nullable()->after('user_id'); // 'deposit', 'withdrawal', 'referral', 'profit', 'investment'
            $table->decimal('amount', 15, 2)->nullable()->after('type');
            $table->string('status')->default('completed')->after('amount'); // 'pending', 'completed', 'failed'
            $table->text('description')->nullable()->after('status');
            $table->string('reference_id')->nullable()->after('description'); // For referral transactions, store the investing user's ID
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['type', 'amount', 'status', 'description', 'reference_id']);
        });
    }
};
