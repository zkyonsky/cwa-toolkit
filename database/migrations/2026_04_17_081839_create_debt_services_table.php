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
        Schema::create('debt_services', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("assessment_id")->unsigned();
            $table->double("plafond")->nullable();
            $table->double('tenor')->nullable();
            $table->double('disbursement_period')->nullable();
            $table->date('first_disbursement')->nullable();
            $table->double('interest')->nullable();
            $table->double('dscr')->nullable();
            $table->double('limit')->nullable();
            $table->double('avg_annual_return')->nullable();
            $table->double('avg_annual_interest')->nullable();
            $table->double('avg_annual_cost')->nullable();
            $table->double('avg_exis_payment')->nullable();
            $table->foreign("assessment_id")->references("id")->on("assessments")->onDelete("cascade");
            $table->text('advantage')->nullable();
            $table->text('challenge')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_services');
    }
};
