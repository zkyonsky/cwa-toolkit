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
        Schema::table('budget_plans', function (Blueprint $table) {
            $table->double('special_autonomy')->nullable()->after('add_profit_sharing_fund_oli_gas_otsus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_plans', function (Blueprint $table) {
            //
        });
    }
};
