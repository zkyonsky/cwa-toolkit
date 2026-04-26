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
        Schema::create('sectoral_gdps', function (Blueprint $table) {
            $table->id();
            $table->integer('gov_code')->unsigned();
            $table->double('agriculture_forestry_fishery')->nullable();
            $table->double('mining_quarrying')->nullable();
            $table->double('processing_industry')->nullable();
            $table->double('electricity_gas')->nullable();
            $table->double('water_waste')->nullable();
            $table->double('contruction')->nullable();
            $table->double('trade_vehicle_repair')->nullable();
            $table->double('transportation_warehousing')->nullable();
            $table->double('acomodation_food_beverage')->nullable();
            $table->double('information_communication')->nullable();
            $table->double('finance_insurance')->nullable();
            $table->double('real_estate')->nullable();
            $table->double('company_service')->nullable();
            $table->double('gov_adm_defense_sosial_security')->nullable();
            $table->double('education_service')->nullable();
            $table->double('health_social_service')->nullable();
            $table->double('other_service')->nullable();
            $table->foreign('gov_code')->references('code')->on('govs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sectoral_gdps');
    }
};
