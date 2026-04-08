<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Torneo extends Model
{
    use HasFactory, SoftDeletes;
    protected $dates = ['deleted_at'];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'modalidad_id' => 'array',
    ];

    protected $fillable = [
        'person_id',
        'ciudad_id',
        'nombre',
        'fechainicio',
        'fechafinal',
        'archivo',
        'estado',
        'deleted_at'
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }
}
