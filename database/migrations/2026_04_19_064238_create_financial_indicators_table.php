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
        Schema::create('financial_indicators', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('assessment_id')->unsigned();
            $table->double('total_revenue')->nullable();
            $table->double('total_pad')->nullable();
            $table->double('pad_growth')->nullable();
            $table->double('pad_revenue')->nullable();
            $table->double('transfer_revenue')->nullable();
            $table->double('other_total_revenue')->nullable();
            $table->double('volatil_pad')->nullable();
            $table->double('operation_revenue')->nullable();
            $table->double('surplus_deficit_before_financing')->nullable();
            $table->double('capital_spending')->nullable();
            $table->double('employee_spending')->nullable();
            $table->double('pad_last_three_year')->nullable();
            $table->double('total_debt')->nullable();
            $table->double('debt_gdp')->nullable();
            $table->double('debt_revenue')->nullable();
            $table->double('ds_revenue')->nullable();
            $table->double('dscr')->nullable();
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_indicators');
    }
};
