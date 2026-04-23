<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;


class Horario extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = ['tipo', 'nombre',
        'dojo_id',
        'status',

        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];
}
