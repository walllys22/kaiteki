<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlumnoEnfermedade extends Model
{
        use HasFactory, SoftDeletes;
        protected $fillable = [
            'alumno_id',
            'enfermedad',
            'medicamento',
            'dosis',
            'observaciones',
            'deleted_at'
        ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

}
