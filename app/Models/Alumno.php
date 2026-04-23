<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumno extends Model
{
        use HasFactory, SoftDeletes;

        protected $fillable = [
            'dojo_id',     
            'person_id',    
            'entry_date',
            'horario_id',
            'grado_id',
            'tipoSangre',
            'status',
            'observacion',
            'deleted_at'
        ];


    /**
     * Los eventos "booted" del modelo.
     */
    protected static function booted()
    {
        static::created(function ($alumno) {
            // Al crear un nuevo alumno, registramos automáticamente su historial de ingreso
            \App\Models\AlumnoHistoriale::create([
                'alumno_id'     => $alumno->id,
                'grado_id'      => $alumno->grado_id,
                'tipo'          => 'Ingreso',
                'aprobo'        => null, // Se guarda vacío
                'fecha'         => $alumno->entry_date,
                'observaciones' => 'ingreso al dojo',
            ]);
        });
    }


    public function dojo()
    {
        return $this->belongsTo(dojo::class, 'dojo_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function horario()
    {
        return $this->belongsTo(horario::class, 'horario_id');
    }

    public function grado()
    {
        return $this->belongsTo(grado::class, 'grado_id');
    }
}