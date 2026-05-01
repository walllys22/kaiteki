<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dojo_id')->nullable()->constrained('dojos')->nullOnDelete();
            $table->foreignId('horario_id')->nullable()->constrained('horarios')->nullOnDelete();
            $table->date('fecha');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreignId('registerUser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('registerRole')->nullable();
            $table->softDeletes();
            $table->foreignId('deleteUser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('deleteRole')->nullable();
            $table->text('deleteObservation')->nullable();

            $table->unique(['dojo_id', 'horario_id', 'fecha'], 'asistencias_dojo_horario_fecha_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
