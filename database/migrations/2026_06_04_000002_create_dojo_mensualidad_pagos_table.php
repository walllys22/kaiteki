<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dojo_mensualidad_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dojo_mensualidad_id')->constrained('dojo_mensualidades');
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('registerUser_id')->nullable();
            $table->string('registerRole')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dojo_mensualidad_pagos');
    }
};
