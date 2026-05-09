<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno_mensualidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('dojo_id')->nullable()->constrained('dojos')->nullOnDelete();
            $table->foreignId('alumno_mensualidad_plan_id')->nullable()->constrained('alumno_mensualidad_planes')->nullOnDelete();
            $table->date('periodo');
            $table->date('fecha_fin')->nullable();
            $table->decimal('monto', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('mora', 10, 2)->default(0);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->text('observacion')->nullable();
            $table->string('status', 30)->default('pendiente');

            $table->timestamps();
            $table->foreignId('registerUser_id')->nullable()->constrained('users');
            $table->string('registerRole')->nullable();

            $table->softDeletes();
            $table->foreignId('deleteUser_id')->nullable()->constrained('users');
            $table->string('deleteRole')->nullable();
            $table->text('deleteObservation')->nullable();

            $table->unique(['alumno_id', 'periodo']);
            $table->index(['dojo_id', 'periodo']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_mensualidades');
    }
};
