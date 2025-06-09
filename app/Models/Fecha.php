<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fecha extends Model
{
    use HasFactory;

    protected $table = 'fecha';
    public $timestamps = true;

    protected $fillable = [
        'fecha',
    ];

    public function sesiones()
    {
        return $this->hasMany(Sesion::class, 'fecha', 'id');
    }
}
