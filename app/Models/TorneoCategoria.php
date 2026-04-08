<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class TorneoCategoria extends Model
{
    use HasFactory, SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'torneo_id',
        'modalidad_id',
        'categoria_id',
        'deleted_at'
    ];


    public function torneo()
    {
        return $this->belongsTo(Torneo::class, 'torneo_id');
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalida::class, 'modalidad_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

}
