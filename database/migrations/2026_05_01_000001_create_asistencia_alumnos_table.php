<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asistencia_id')->constrained('asistencias')->cascadeOnDelete();
            $table->foreignId('alumno_id')->nullable()->constrained('alumnos')->nullOnDelete();
            $table->enum('estado', ['asistencia', 'licencia', 'falta'])->default('asistencia');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreignId('registerUser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('registerRole')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_alumnos');
    }
};
