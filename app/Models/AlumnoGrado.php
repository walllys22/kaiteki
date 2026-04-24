<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;

class AlumnoGrado extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'alumno_id',
        'grado_id',
        'fecha',
        'observacion',
        // '0' = en progreso, '1' = completado
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
        return $this->belongsTo(Grado::class, 'grado_id')->withTrashed();
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function repasos()
    {
        return $this->hasMany(AlumnoGradoRepaso::class, 'alumno_grado_id')->whereNull('deleted_at');
    }

    public function examenes()
    {
        return $this->hasMany(AlumnoGradoExamen::class, 'alumno_grado_id')->whereNull('deleted_at');
    }

    public function isCompletado(): bool
    {
        return (string) $this->status === '1';
    }
}
