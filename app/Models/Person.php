<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;

class Person extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'documentType',
        'dojo_id',
        'ci',
        'first_name',
        // 'middle_name',
        // 'paternal_surname',
        // 'maternal_surname',
        'birth_date',
        'email',
        'country_code',
        'phone',
        'address',
        'gender',
        'sangre',
        'image',
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
        return $this->belongsTo(Dojo::class, 'dojo_id');
    }

    public function dojoId()
    {
        return $this->dojo();
    }

    public function alumno()
    {
        return $this->hasMany(Alumno::class, 'person_id');
    }
    
    public function register()
    {
        return $this->belongsTo(User::class, 'registerUser_id');
    }
}
