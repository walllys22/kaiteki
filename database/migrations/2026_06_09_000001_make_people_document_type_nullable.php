<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('people', 'documentType')) {
            DB::statement('ALTER TABLE people MODIFY documentType VARCHAR(255) NULL');
        }

        $this->updateBreadValidation(false, 'nullable|in:Ci,Nit');
    }

    public function down(): void
    {
        if (Schema::hasColumn('people', 'documentType')) {
            DB::table('people')->whereNull('documentType')->update(['documentType' => 'Ci']);
            DB::statement('ALTER TABLE people MODIFY documentType VARCHAR(255) NOT NULL');
        }

        $this->updateBreadValidation(true, 'required');
    }

    private function updateBreadValidation(bool $required, string $rule): void
    {
        $row = DB::table('data_rows')
            ->join('data_types', 'data_rows.data_type_id', '=', 'data_types.id')
            ->where('data_types.name', 'people')
            ->where('data_rows.field', 'documentType')
            ->select('data_rows.id', 'data_rows.details')
            ->first();

        if (! $row) {
            return;
        }

        $details = json_decode($row->details ?: '{}', true) ?: [];
        $details['validation']['rule'] = $rule;

        DB::table('data_rows')
            ->where('id', $row->id)
            ->update([
                'required' => $required ? 1 : 0,
                'details' => json_encode($details),
            ]);
    }
};
