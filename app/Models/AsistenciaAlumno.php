<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistersUserEvents;

class AsistenciaAlumno extends Model
{
    use HasFactory, RegistersUserEvents;

    protected $fillable = [
        'asistencia_id', 'alumno_id', 'estado', 'observacion',
        'registerUser_id', 'registerRole',
    ];

    public function asistencia()
    {
        return $this->belongsTo(Asistencia::class, 'asistencia_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }
}
