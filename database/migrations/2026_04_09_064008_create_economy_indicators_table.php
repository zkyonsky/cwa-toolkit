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
        Schema::create('economy_indicators', function (Blueprint $table) {
            $table->id();
            $table->integer("gov_code")->unsigned();
            $table->double("poverty")->nullable();
            $table->double("unemployment")->nullable();
            $table->double("gdp_growth")->nullable();
            $table->double("gdp_perkapita")->nullable();
            $table->double("hdci")->nullable();
            $table->double("infras_real")->nullable();
            $table->double("fiscal_ratio")->nullable();
            $table->integer("year");
            $table->foreign("gov_code")->references("code")->on("govs")->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('economy_indicators');
    }
};
