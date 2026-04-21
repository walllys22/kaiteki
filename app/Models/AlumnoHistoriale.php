<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlumnoHistoriale extends Model
{
        use HasFactory, SoftDeletes;
        protected $table = 'alumnohistoriales';
        protected $fillable = [
            'alumno_id',
            'grado_id',
            'tipo',
            'aprobo',
            'fecha',
            'observaciones',
            'deleted_at'
        ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }
    
        public function grado()
    {
        return $this->belongsTo(grado::class, 'grado_id');
    }
}

