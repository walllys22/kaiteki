<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\RegistersUserEvents;
use App\Models\Dojo;


class User extends \TCG\Voyager\Models\User
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, RegistersUserEvents;

    protected $dates = ['deleted_at'];


    protected $fillable = [
        'person_id',
        'dojo_id',
        'name',
        'role_id',
        'email',
        'password',
        'status',
        
        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function dojo()
    {
        return $this->belongsTo(Dojo::class, 'dojo_id');
    }

    /**
     * Clave de sesion donde el admin global guarda el dojo que esta mirando.
     */
    public const DOJO_ACTIVO_SESSION_KEY = 'dojo_activo_id';

    /**
     * Un usuario es global cuando su dojo_id REAL en base de datos es null
     * (roles admin / administrador). No usar $this->dojo_id para esto: ese
     * accessor devuelve el dojo elegido en el sidebar.
     */
    public function isGlobal(): bool
    {
        return $this->getRawOriginal('dojo_id') === null;
    }

    /**
     * dojo_id efectivo:
     * - operador de sucursal -> siempre su dojo real, no puede cambiarlo
     * - admin global         -> el dojo elegido en el sidebar, o null (todos)
     *
     * Todo el sistema lee auth()->user()->dojo_id, asi que el filtrado por
     * sucursal elegida funciona sin tocar los controladores.
     */
    public function getDojoIdAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }

        if (! app()->bound('session')) {
            return null;
        }

        try {
            return session(self::DOJO_ACTIVO_SESSION_KEY);
        } catch (\Throwable $e) {
            // Sin sesion disponible (colas, comandos): sin dojo activo.
            return null;
        }
    }


    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
