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
        Schema::create('budget_plans', function (Blueprint $table) {
            $table->id();
            $table->integer('gov_code')->unsigned();
            $table->double("self_revenue")->nullable();
            $table->double("vehicle_tax")->nullable();
            $table->double("shared_vehicle_tax")->nullable();
            $table->double("cigarette_tax")->nullable();
            $table->double("shared_cigarette_tax")->nullable();
            $table->double("electricity_tax")->nullable();
            $table->double("street_lighting_tax")->nullable();
            $table->double("opsen_vehicle_tax")->nullable();
            $table->double("blud_revenue")->nullable();
            $table->double("general_allocation_fund")->nullable();
            $table->double("general_allocation_fund_district")->nullable();
            $table->double("general_allocation_fund_education")->nullable();
            $table->double("general_allocation_fund_health")->nullable();
            $table->double("general_allocation_fund_public_work")->nullable();
            $table->double("profit_sharing_fund")->nullable();
            $table->double("profit_sharing_fund_cigarette")->nullable();
            $table->double("profit_sharing_fund_reboisation")->nullable();
            $table->double("profit_sharing_fund_sawit")->nullable();
            $table->double("add_profit_sharing_fund_oli_gas_otsus")->nullable();
            $table->double("inter_regional_transfer_revenue")->nullable();
            $table->double("other_revenue")->nullable();
            $table->double("central_gov_grant")->nullable();
            $table->double("national_health_revenue")->nullable();
            $table->double("sharing_fund_spending")->nullable();
            $table->double("village_fund_allocation")->nullable();
            $table->double("employee_spending")->nullable();
            $table->double("teacher_non_certification_allowance")->nullable();
            $table->double("teacher_certification_allowance")->nullable();
            $table->double("regional_teacher_additional_allowance")->nullable();
            $table->foreign('gov_code')->references('code')->on('govs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_plans');
    }
};
