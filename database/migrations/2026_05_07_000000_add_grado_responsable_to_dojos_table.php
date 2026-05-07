<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dojos', function (Blueprint $table) {
            if (!Schema::hasColumn('dojos', 'grado_responsable')) {
                $table->string('grado_responsable')->nullable()->after('logo');
            }
        });

        $dataTypeId = DB::table('data_types')->where('name', 'dojos')->value('id');

        if ($dataTypeId && !DB::table('data_rows')->where('data_type_id', $dataTypeId)->where('field', 'grado_responsable')->exists()) {
            DB::table('data_rows')
                ->where('data_type_id', $dataTypeId)
                ->where('order', '>=', 4)
                ->increment('order');

            DB::table('data_rows')->insert([
                'data_type_id' => $dataTypeId,
                'field' => 'grado_responsable',
                'type' => 'text',
                'display_name' => 'Grado',
                'required' => 0,
                'browse' => 1,
                'read' => 1,
                'edit' => 1,
                'add' => 1,
                'delete' => 0,
                'details' => '{"validation":{"rule":"nullable|max:191"},"display":{"width":6}}',
                'order' => 4,
            ]);
        }
    }

    public function down(): void
    {
        $dataTypeId = DB::table('data_types')->where('name', 'dojos')->value('id');

        if ($dataTypeId) {
            DB::table('data_rows')
                ->where('data_type_id', $dataTypeId)
                ->where('field', 'grado_responsable')
                ->delete();

            DB::table('data_rows')
                ->where('data_type_id', $dataTypeId)
                ->where('order', '>', 4)
                ->decrement('order');
        }

        Schema::table('dojos', function (Blueprint $table) {
            if (Schema::hasColumn('dojos', 'grado_responsable')) {
                $table->dropColumn('grado_responsable');
            }
        });
    }
};
