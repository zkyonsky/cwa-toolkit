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
        Schema::create('infras_conclusions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("assessment_id")->unsigned();
            $table->text("advantage");
            $table->text("challenge");
            $table->timestamps();
            $table->foreign("assessment_id")->references("id")->on("assessments")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infras_conclusions');
    }
};
