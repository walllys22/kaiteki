<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;


class Alumno extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'dojo_id',
        'person_id',
        'fechaIngreso',

        'observacion',
        'status',

        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    public function dojo()
    {
        return $this->belongsTo(Dojo::class, 'dojo_id')->withTrashed();
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id')->withTrashed();
    }
    public function alumnoEnfermedads()
    {
        return $this->hasMany(AlumnoEnfermedad::class, 'alumno_id');
    }
    public function alumnoGrado()
    {
        return $this->hasMany(AlumnoGrado::class, 'alumno_id');
    }

    public function alumnoHorarios()
    {
        return $this->hasMany(AlumnoHorario::class, 'alumno_id');
    }

    public function ultimoGrado()
    {
        return $this->hasOne(AlumnoGrado::class, 'alumno_id')->latestOfMany();
    }

    public function mensualidadPlan()
    {
        return $this->hasOne(AlumnoMensualidadPlan::class, 'alumno_id')->where('status', 1);
    }

    public function mensualidades()
    {
        return $this->hasMany(AlumnoMensualidad::class, 'alumno_id');
    }

    public function register()
    {
        return $this->belongsTo(User::class, 'registerUser_id');
    }
}
