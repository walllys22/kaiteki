<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DojoMensualidadPago extends Model
{
    use HasFactory;

    protected $table = 'dojo_mensualidad_pagos';

    protected $casts = [
        'fecha' => 'date',
    ];

    protected $fillable = [
        'dojo_mensualidad_id',
        'monto',
        'fecha',
        'observacion',
        'registerUser_id',
        'registerRole',
    ];

    public function mensualidad()
    {
        return $this->belongsTo(DojoMensualidad::class, 'dojo_mensualidad_id');
    }

    public function registerUser()
    {
        return $this->belongsTo(User::class, 'registerUser_id')->withTrashed();
    }
}
