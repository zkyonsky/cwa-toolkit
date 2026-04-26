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
        Schema::create('self_assessments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('assessment_id')->unsigned();
            $table->string('social_politics_disturbance')->nullable();
            $table->string('social_politics_disturbance_detail')->nullable();
            $table->string('arrears_restructuring')->nullable();
            $table->string('arrears_restructuring_detail')->nullable();
            $table->string('cashflow_availability')->nullable();
            $table->string('cashflow_availability_detail')->nullable();
            $table->text('advantage')->nullable();
            $table->text('challenge')->nullable();
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('self_assessments');
    }
};
