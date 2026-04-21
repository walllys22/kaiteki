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
        Schema::create('alumnohistoriales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->nullable()->constrained('alumnos');
            $table->foreignId('grado_id')->nullable()->constrained('grados');
            $table->string('tipo')->nullable();
            $table->string('aprobo')->nullable();
            $table->date('fecha')->nullable();
            $table->string('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnohistoriales');
    }
};
