<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dojo_mensualidades', function (Blueprint $table) {
            $table->renameColumn('periodo', 'fecha_inicio');
            $table->renameColumn('fecha_vencimiento', 'fecha_fin');
        });
    }

    public function down(): void
    {
        Schema::table('dojo_mensualidades', function (Blueprint $table) {
            $table->renameColumn('fecha_inicio', 'periodo');
            $table->renameColumn('fecha_fin', 'fecha_vencimiento');
        });
    }
};
