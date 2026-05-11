<?php

namespace App\Models;

use App\Traits\RegistersUserEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlumnoMensualidad extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $table = 'alumno_mensualidades';

    protected $dates = ['deleted_at'];

    protected $casts = [
        'periodo' => 'date',
        'fecha_fin' => 'date',
    ];

    protected $fillable = [
        'alumno_id',
        'dojo_id',
        'alumno_mensualidad_plan_id',
        'periodo',
        'fecha_fin',
        'monto',
        'descuento',
        'monto_pagado',
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

    public function plan()
    {
        return $this->belongsTo(AlumnoMensualidadPlan::class, 'alumno_mensualidad_plan_id');
    }

    public function pagos()
    {
        return $this->hasMany(AlumnoMensualidadPago::class, 'alumno_mensualidad_id');
    }

    public function total(): float
    {
        return max(0, (float) $this->monto - (float) $this->descuento);
    }

    public function saldo(): float
    {
        return max(0, $this->total() - (float) $this->monto_pagado);
    }

    public function estadoPago(): string
    {
        if ($this->status === 'anulado') {
            return 'Anulado';
        }

        if ($this->total() <= 0) {
            return 'Exonerado';
        }

        if ((float) $this->monto_pagado >= $this->total()) {
            return 'Pagado';
        }

        if ((float) $this->monto_pagado > 0) {
            return 'Parcial';
        }

        return 'Pendiente';
    }
}
