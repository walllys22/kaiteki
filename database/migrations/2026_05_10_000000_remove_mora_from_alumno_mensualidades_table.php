<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_mensualidades', function (Blueprint $table) {
            if (Schema::hasColumn('alumno_mensualidades', 'mora')) {
                $table->dropColumn('mora');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumno_mensualidades', function (Blueprint $table) {
            if (!Schema::hasColumn('alumno_mensualidades', 'mora')) {
                $table->decimal('mora', 10, 2)->default(0)->after('descuento');
            }
        });
    }
};
