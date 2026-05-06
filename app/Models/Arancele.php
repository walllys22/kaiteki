<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;

class Arancele extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'grado_id',
        'dojo_id',
        'tipo',
        'precio',
        'observacion',
        'status',
        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    public function dojo()
    {
        return $this->belongsTo(Dojo::class, 'dojo_id');
    }
}
