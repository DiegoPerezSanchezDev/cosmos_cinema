<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hora extends Model
{
    use HasFactory;

    protected $table = 'hora';
    public $timestamps = true;

    protected $fillable = [
        'hora',
    ];

    public function sesiones()
    {
        return $this->hasMany(Sesion::class, 'hora', 'id');
    }
}
