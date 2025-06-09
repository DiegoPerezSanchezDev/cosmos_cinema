<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    use HasFactory;

    protected $table = 'sala';
    protected $primaryKey = 'id_sala';
    public $timestamps = false;

    protected $fillable = [
        'numero_asientos',
    ];

    public function sesiones()
    {
        return $this->hasMany(Sesion::class);
    }
}
