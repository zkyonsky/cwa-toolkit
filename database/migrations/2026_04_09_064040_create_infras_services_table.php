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
        Schema::create('infras_services', function (Blueprint $table) {
            $table->id();
            $table->text("education");
            $table->text("health");
            $table->text("water");
            $table->text("waste");
            $table->text("it");
            $table->text("agriculture");
            $table->text("transport");
            $table->text("electricity");
            $table->text("sport_art_culture");
            $table->text("tourism");
            $table->text("food");
            $table->text("commerce");
            $table->text("road");
            $table->timestamps();
            $table->foreignId("assessment_id")->constrained("assessments")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infras_services');
    }
};
