<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;

class Asistencia extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $fillable = [
        'dojo_id', 'horario_id', 'fecha', 'observacion',
        'registerUser_id', 'registerRole',
        'deleteUser_id', 'deleteRole', 'deleteObservation',
    ];

    protected $casts = ['fecha' => 'date'];

    public function dojo()
    {
        return $this->belongsTo(Dojo::class, 'dojo_id');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }

    public function detalles()
    {
        return $this->hasMany(AsistenciaAlumno::class, 'asistencia_id');
    }

    public function register()
    {
        return $this->belongsTo(User::class, 'registerUser_id')->withTrashed();
    }
}
