<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dojo_mensualidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dojo_id')->constrained('dojos');
            $table->date('periodo');
            $table->date('fecha_vencimiento');
            $table->decimal('monto', 10, 2)->default(0);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('registerUser_id')->nullable();
            $table->string('registerRole')->nullable();
            $table->unsignedBigInteger('deleteUser_id')->nullable();
            $table->string('deleteRole')->nullable();
            $table->text('deleteObservation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dojo_mensualidades');
    }
};
