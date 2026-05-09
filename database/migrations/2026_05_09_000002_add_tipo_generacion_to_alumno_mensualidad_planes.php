<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_mensualidad_planes', function (Blueprint $table) {
            if (!Schema::hasColumn('alumno_mensualidad_planes', 'fecha_fin')) {
                $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            }

            if (!Schema::hasColumn('alumno_mensualidad_planes', 'tipo_generacion')) {
                $table->string('tipo_generacion', 30)->default('automatica')->after('fecha_fin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumno_mensualidad_planes', function (Blueprint $table) {
            if (Schema::hasColumn('alumno_mensualidad_planes', 'tipo_generacion')) {
                $table->dropColumn('tipo_generacion');
            }

            if (Schema::hasColumn('alumno_mensualidad_planes', 'fecha_fin')) {
                $table->dropColumn('fecha_fin');
            }
        });
    }
};
