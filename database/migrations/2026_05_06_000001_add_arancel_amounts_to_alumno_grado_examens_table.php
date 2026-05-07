<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_grado_examens', function (Blueprint $table) {
            if (!Schema::hasColumn('alumno_grado_examens', 'arancel_id')) {
                $table->foreignId('arancel_id')->nullable()->constrained('aranceles')->nullOnDelete();
            }

            if (!Schema::hasColumn('alumno_grado_examens', 'monto')) {
                $table->decimal('monto', 10, 2)->default(0);
            }

            if (!Schema::hasColumn('alumno_grado_examens', 'monto_pagado')) {
                $table->decimal('monto_pagado', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumno_grado_examens', function (Blueprint $table) {
            if (Schema::hasColumn('alumno_grado_examens', 'arancel_id')) {
                $table->dropConstrainedForeignId('arancel_id');
            }

            if (Schema::hasColumn('alumno_grado_examens', 'monto')) {
                $table->dropColumn('monto');
            }

            if (Schema::hasColumn('alumno_grado_examens', 'monto_pagado')) {
                $table->dropColumn('monto_pagado');
            }
        });
    }
};
