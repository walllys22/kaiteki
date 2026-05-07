<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;

class AlumnoGradoExamen extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'alumno_grado_id',
        'arancel_id',
        'fecha',
        'aprobado',
        'monto',
        'monto_pagado',
        'observacion',
        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    public function alumnoGrado()
    {
        return $this->belongsTo(AlumnoGrado::class, 'alumno_grado_id');
    }

    public function arancel()
    {
        return $this->belongsTo(Arancele::class, 'arancel_id');
    }
}
