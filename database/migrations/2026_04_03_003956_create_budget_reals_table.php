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
        Schema::create('budget_reals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('gov_id')->unsigned();
            $table->string('bpk_opinion');
            $table->string('input_status');
            $table->double('income_after_cleansing');
            $table->double('pad_after_cleansing');
            $table->double('tax_income');
            $table->double('retribution_income');
            $table->double('asset_income');
            $table->double('other_pad');
            $table->double('transfer_income');
            $table->double('other_legitimate_income');
            $table->double('other_income');
            $table->double('spending_after_cleansing');
            $table->double('operational_spending');
            $table->double('employee_spending');
            $table->double('good_service_spending');
            $table->double('interest_spending');
            $table->double('subsidy_spending');
            $table->double('grant_spending');
            $table->double('social_spending');
            $table->double('capital_spending');
            $table->double('land_spending');
            $table->double('machine_spending');
            $table->double('building_spending');
            $table->double('infrastructure_spending');
            $table->double('other_asset_spending');
            $table->double('unexpected_spending');
            $table->integer('year');
            $table->timestamps();
            $table->foreign('gov_id')->references('id')->on('govs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_reals');
    }
};
