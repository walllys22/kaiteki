<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;

class AlumnoTutor extends Model
{

    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'alumno_id',
        'person_id',
        'parentesco_id',

        'observacion',
        'status',

        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    public function tutor()
    {
        return $this->belongsTo(Person::class, 'person_id')->withTrashed();
    }

    public function pariente()
    {
        return $this->belongsTo(Parentesco::class, 'parentesco_id')->withTrashed();
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }
}
