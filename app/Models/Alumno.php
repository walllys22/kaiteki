<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumno extends Model
{
        use HasFactory, SoftDeletes;

        protected $fillable = [
            'dojo_id',     
            'person_id',    
            'entry_date',
            'horario_id',
            'grado_id',
            'status',
            'observacion',
            'foto',
            'deleted_at'
        
          ];



    public function dojo()
    {
        return $this->belongsTo(dojo::class, 'dojo_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function horario()
    {
        return $this->belongsTo(horario::class, 'horario_id');
    }

    public function grado()
    {
        return $this->belongsTo(grado::class, 'grado_id');
    }
}