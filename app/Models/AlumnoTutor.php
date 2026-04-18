<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class alumnotutor extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'alumno_id',
        'tutor_id',
        'pariente_id',
        'observacion',
        'deleted_at'
    ];
    

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function tutor()
    {
        return $this->belongsTo(Person::class, 'tutor_id');
    }

    public function pariente()
    {
        return $this->belongsTo(Parentesco::class, 'pariente_id');
    }


}
