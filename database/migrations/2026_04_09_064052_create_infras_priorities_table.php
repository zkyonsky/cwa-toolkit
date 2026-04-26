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
        Schema::create('infras_priorities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("assessment_id")->unsigned();
            $table->text("plan");
            $table->text("exp_outcome");
            $table->integer("rank");
            $table->double("estimated_cost");
            $table->string("fund_source");
            $table->double("alt_fund_need");
            $table->string("alt_fund_source");
            $table->timestamps();
            $table->foreign("assessment_id")->references("id")->on("assessments")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infras_priorities');
    }
};
