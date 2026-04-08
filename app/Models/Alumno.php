<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumno extends Model
{
        use HasFactory, SoftDeletes;

        protected $fillable = [
            'person_id',    
            'entry_date',
            'horario_id',
            'grado_id',
            'status',
            'obsernacion',
            'foto',
            'deleted_at'
        
          ];

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