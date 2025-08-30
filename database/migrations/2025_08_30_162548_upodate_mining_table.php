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
        Schema::table('mining_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('investment_id')->nullable()->after('user_id');
            $table->foreign('investment_id')->references('id')->on('investments')->onDelete('set null');

            if (!Schema::hasColumn('mining_sessions', 'stopped_at')) {
                $table->timestamp('stopped_at')->nullable();
            }
            if (!Schema::hasColumn('mining_sessions', 'rewards_claimed')) {
                $table->boolean('rewards_claimed')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('mining_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('mining_sessions', 'investment_id')) {
                $table->dropForeign(['investment_id']);
                $table->dropColumn('investment_id');
            }
            if (Schema::hasColumn('mining_sessions', 'stopped_at')) {
                $table->dropColumn('stopped_at');
            }
            if (Schema::hasColumn('mining_sessions', 'rewards_claimed')) {
                $table->dropColumn('rewards_claimed');
            }
        });
    }
};
