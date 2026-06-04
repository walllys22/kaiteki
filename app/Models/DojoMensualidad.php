<?php

namespace App\Models;

use App\Traits\RegistersUserEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class DojoMensualidad extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $table = 'dojo_mensualidades';

    protected $dates = ['deleted_at'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    protected $fillable = [
        'dojo_id',
        'fecha_inicio',
        'fecha_fin',
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

    public function dojo()
    {
        return $this->belongsTo(Dojo::class, 'dojo_id')->withTrashed();
    }

    public function pagos()
    {
        return $this->hasMany(DojoMensualidadPago::class, 'dojo_mensualidad_id')->orderBy('fecha')->orderBy('id');
    }

    public function isVigente(): bool
    {
        return $this->fecha_fin >= Carbon::today();
    }

    public function estadoPago(): string
    {
        if ((float) $this->monto_pagado >= (float) $this->monto) {
            return 'Pagado';
        }

        if ($this->fecha_fin < Carbon::today()) {
            return 'Vencido';
        }

        return 'Pendiente';
    }

    public function saldo(): float
    {
        return max(0, (float) $this->monto - (float) $this->monto_pagado);
    }
}
