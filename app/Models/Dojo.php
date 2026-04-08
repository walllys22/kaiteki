<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dojo extends Model
{
        use HasFactory, SoftDeletes;

        protected $fillable = [
            'nombre',
            'logo',
            'person_id',
            'ciudad_id',
            'phone',
            'address',
            'email',
            'status',
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
