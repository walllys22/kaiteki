<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;

class Dojo extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = ['nombre', 'logo', 'grado_responsable', 'person_id', 'ciudad_id', 'phone', 'address', 'email', 'status', 'registerUser_id', 'registerRole', 'deleted_at', 'deleteUser_id', 'deleteRole', 'deleteObservation'];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'dojo_id');
    }

    public function people()
    {
        return $this->hasMany(Person::class, 'dojo_id');
    }

    public function aranceles()
    {
        return $this->hasMany(Arancele::class, 'dojo_id');
    }
}
