<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno_grado_repasos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_grado_id')->constrained('alumno_grados')->onDelete('cascade');
            $table->date('fecha');
            $table->boolean('aprobado')->default(false);
            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->foreignId('registerUser_id')->nullable()->constrained('users');
            $table->string('registerRole')->nullable();
            $table->softDeletes();
            $table->foreignId('deleteUser_id')->nullable()->constrained('users');
            $table->string('deleteRole')->nullable();
            $table->text('deleteObservation')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_grado_repasos');
    }
};
