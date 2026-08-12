<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add gov_code (FK to govs.code) to budget_reals.
     * This column exists in production but was not in the original migrations.
     * Note: total_transfer is already added by 2026_04_20_032701 migration.
     */
    public function up(): void
    {
        Schema::table('budget_reals', function (Blueprint $table) {
            $table->integer('gov_code')->unsigned()->nullable()->after('id');
            $table->foreign('gov_code')->references('code')->on('govs')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('budget_reals', function (Blueprint $table) {
            $table->dropForeign(['gov_code']);
            $table->dropColumn('gov_code');
        });
    }
};

