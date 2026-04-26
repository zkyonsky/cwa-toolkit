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
            $table->double('income_after_cleansing')->nullable()->change();
            $table->double('pad_after_cleansing')->nullable()->change();
            $table->double('tax_income')->nullable()->change();
            $table->double('retribution_income')->nullable()->change();
            $table->double('asset_income')->nullable()->change();
            $table->double('other_pad')->nullable()->change();
            $table->double('transfer_income')->nullable()->change();
            $table->double('other_legitimate_income')->nullable()->change();
            $table->double('other_income')->nullable()->change();
            $table->double('spending_after_cleansing')->nullable()->change();
            $table->double('operational_spending')->nullable()->change();
            $table->double('employee_spending')->nullable()->change();
            $table->double('good_service_spending')->nullable()->change();
            $table->double('interest_spending')->nullable()->change();
            $table->double('subsidy_spending')->nullable()->change();
            $table->double('grant_spending')->nullable()->change();
            $table->double('social_spending')->nullable()->change();
            $table->double('capital_spending')->nullable()->change();
            $table->double('land_spending')->nullable()->change();
            $table->double('machine_spending')->nullable()->change();
            $table->double('building_spending')->nullable()->change();
            $table->double('infrastructure_spending')->nullable()->change();
            $table->double('other_fix_asset_spending')->nullable();
            $table->double('other_asset_spending')->nullable()->change();
            $table->double('unexpected_spending')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_reals', function (Blueprint $table) {
            //
        });
    }
};
