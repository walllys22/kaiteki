<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno_mensualidad_planes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('dojo_id')->nullable()->constrained('dojos')->nullOnDelete();
            $table->decimal('monto_mensual', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('beca', 10, 2)->default(0);
            $table->date('fecha_inicio');
            $table->text('observacion')->nullable();
            $table->smallInteger('status')->default(1);

            $table->timestamps();
            $table->foreignId('registerUser_id')->nullable()->constrained('users');
            $table->string('registerRole')->nullable();

            $table->softDeletes();
            $table->foreignId('deleteUser_id')->nullable()->constrained('users');
            $table->string('deleteRole')->nullable();
            $table->text('deleteObservation')->nullable();

            $table->index(['alumno_id', 'status']);
            $table->index(['dojo_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_mensualidad_planes');
    }
};
