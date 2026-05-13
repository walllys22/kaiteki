<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;

class Grado extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'tipo', 'numero', 'nombre',
        'image',

        'puntas',
        'dias',

        'status',
        'orden',

        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    public function alumnoGrados()
    {
        return $this->hasMany(AlumnoGrado::class, 'grado_id');
    }

    public function aranceles()
    {
        return $this->hasMany(Arancele::class, 'grado_id');
    }

    public function scopeOrdenado($query)
    {
        return $query->orderByRaw('COALESCE(orden, 99999) ASC, id ASC');
    }

    public function isDan(): bool
    {
        return strtolower(trim((string) $this->tipo)) === 'dan';
    }

    public function usaRepasos(): bool
    {
        return !$this->isDan();
    }
}
