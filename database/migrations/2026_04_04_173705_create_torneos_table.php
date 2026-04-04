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
        Schema::create('torneos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->nullable()->constrained('people');
            $table->foreignId('ciudad_id')->nullable()->constrained('ciudads');
            $table->string('nombre')->nullable();
            $table->date('fechainicio')->nullable();
            $table->date('fechafinal')->nullable();
            $table->string('archivo')->nullable();


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('torneos');
    }
};
