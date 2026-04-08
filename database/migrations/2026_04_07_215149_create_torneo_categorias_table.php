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
        Schema::create('torneo_categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('torneo_id')->nullable()->constrained('torneos');
            $table->foreignId('modalidad_id')->nullable()->constrained('modalidas');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('torneo_categorias');
    }
};
