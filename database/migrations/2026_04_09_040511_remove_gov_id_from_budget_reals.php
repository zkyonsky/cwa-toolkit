<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budget_reals', function (Blueprint $table) {
            $table->dropForeign(['gov_id']);
            $table->dropColumn('gov_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_reals', function (Blueprint $table) {
            $table->bigInteger('gov_id')->unsigned();
            $table->foreign('gov_id')->references('id')->on('govs')->onDelete('cascade');
        });
    }
};
