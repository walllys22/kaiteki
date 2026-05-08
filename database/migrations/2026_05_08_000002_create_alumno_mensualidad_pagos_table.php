<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno_mensualidad_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_mensualidad_id')->constrained('alumno_mensualidades')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('dojo_id')->nullable()->constrained('dojos')->nullOnDelete();
            $table->date('fecha');
            $table->decimal('monto', 10, 2)->default(0);
            $table->text('observacion')->nullable();

            $table->timestamps();
            $table->foreignId('registerUser_id')->nullable()->constrained('users');
            $table->string('registerRole')->nullable();

            $table->softDeletes();
            $table->foreignId('deleteUser_id')->nullable()->constrained('users');
            $table->string('deleteRole')->nullable();
            $table->text('deleteObservation')->nullable();

            $table->index(['alumno_id', 'fecha']);
            $table->index(['dojo_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_mensualidad_pagos');
    }
};
