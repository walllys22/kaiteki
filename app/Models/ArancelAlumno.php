<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArancelAlumno extends Model
{
    use HasFactory, SoftDeletes;
    protected $dates = ['deleted_at'];

        protected $fillable = [
        'alumno_id',
        'arancel_id',
        'monto',
        'status',
        'deleted_at'
          ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function arancel()
    {
        return $this->belongsTo(Arancele::class, 'arancel_id');
    }


}
