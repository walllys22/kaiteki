<?php

namespace App\Models;

use App\Traits\RegistersUserEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlumnoMensualidadPago extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $table = 'alumno_mensualidad_pagos';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'alumno_mensualidad_id',
        'alumno_id',
        'dojo_id',
        'fecha',
        'monto',
        'observacion',
        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    public function mensualidad()
    {
        return $this->belongsTo(AlumnoMensualidad::class, 'alumno_mensualidad_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function registerUser()
    {
        return $this->belongsTo(User::class, 'registerUser_id');
    }
}
