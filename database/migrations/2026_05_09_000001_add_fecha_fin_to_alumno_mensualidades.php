<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('alumno_mensualidades', 'fecha_fin')) {
            Schema::table('alumno_mensualidades', function (Blueprint $table) {
                $table->date('fecha_fin')->nullable()->after('periodo');
            });
        }

        DB::table('alumno_mensualidades')
            ->whereNull('fecha_fin')
            ->orderBy('id')
            ->chunkById(100, function ($mensualidades) {
                foreach ($mensualidades as $mensualidad) {
                    if (!$mensualidad->periodo) {
                        continue;
                    }

                    DB::table('alumno_mensualidades')
                        ->where('id', $mensualidad->id)
                        ->update([
                            'fecha_fin' => Carbon::parse($mensualidad->periodo)
                                ->startOfDay()
                                ->addMonthNoOverflow()
                                ->subDay()
                                ->toDateString(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('alumno_mensualidades', 'fecha_fin')) {
            Schema::table('alumno_mensualidades', function (Blueprint $table) {
                $table->dropColumn('fecha_fin');
            });
        }
    }
};
