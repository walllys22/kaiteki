<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateCiValidation('nullable|max:191|unique:people,ci');
    }

    public function down(): void
    {
        $this->updateCiValidation('required|max:191|unique:people');
    }

    private function updateCiValidation(string $rule): void
    {
        $row = DB::table('data_rows')
            ->join('data_types', 'data_rows.data_type_id', '=', 'data_types.id')
            ->where('data_types.name', 'people')
            ->where('data_rows.field', 'ci')
            ->select('data_rows.id', 'data_rows.details')
            ->first();

        if (! $row) {
            return;
        }

        $details = json_decode($row->details ?: '{}', true) ?: [];
        $details['validation']['rule'] = $rule;

        DB::table('data_rows')
            ->where('id', $row->id)
            ->update(['details' => json_encode($details)]);
    }
};
