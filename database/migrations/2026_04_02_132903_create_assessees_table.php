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
        Schema::create('assessees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('gov_id')->unsigned();
            $table->string('name');
            $table->string('position');
            $table->string('contact');
            $table->string('address');
            $table->timestamps();
            $table->foreign('gov_id')->references('id')->on('govs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessees');

    }
};
