<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;

class AlumnoHorario extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $fillable = [
        'alumno_id',
        'horario_id',
        'status',
        'observacion',
        'registerUser_id',
        'registerRole',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }
}
