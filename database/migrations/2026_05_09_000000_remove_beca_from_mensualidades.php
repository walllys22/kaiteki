<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_mensualidad_planes', function (Blueprint $table) {
            if (Schema::hasColumn('alumno_mensualidad_planes', 'beca')) {
                $table->dropColumn('beca');
            }
        });

        Schema::table('alumno_mensualidades', function (Blueprint $table) {
            if (Schema::hasColumn('alumno_mensualidades', 'beca')) {
                $table->dropColumn('beca');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumno_mensualidad_planes', function (Blueprint $table) {
            if (!Schema::hasColumn('alumno_mensualidad_planes', 'beca')) {
                $table->decimal('beca', 10, 2)->default(0)->after('descuento');
            }
        });

        Schema::table('alumno_mensualidades', function (Blueprint $table) {
            if (!Schema::hasColumn('alumno_mensualidades', 'beca')) {
                $table->decimal('beca', 10, 2)->default(0)->after('descuento');
            }
        });
    }
};
