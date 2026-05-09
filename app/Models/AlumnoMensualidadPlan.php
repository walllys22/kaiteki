<?php

namespace App\Models;

use App\Traits\RegistersUserEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlumnoMensualidadPlan extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $table = 'alumno_mensualidad_planes';

    protected $dates = ['deleted_at'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    protected $fillable = [
        'alumno_id',
        'dojo_id',
        'monto_mensual',
        'descuento',
        'fecha_inicio',
        'fecha_fin',
        'tipo_generacion',
        'observacion',
        'status',
        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function dojo()
    {
        return $this->belongsTo(Dojo::class, 'dojo_id')->withTrashed();
    }

    public function mensualidades()
    {
        return $this->hasMany(AlumnoMensualidad::class, 'alumno_mensualidad_plan_id');
    }
}
