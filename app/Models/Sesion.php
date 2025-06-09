<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    use HasFactory;

    protected $table = 'sesion_pelicula';
    public $timestamps = true;

    protected $fillable = [
        'id_sala',
        'id_pelicula',
        'fecha',
        'hora',
        'activa',
    ];

    // Relación con la película
    public function pelicula()
    {
        return $this->belongsTo(Pelicula::class, 'id_pelicula', 'id');
    }

    // Relación con la sala
    public function sala()
    {
        return $this->belongsTo(Sala::class, 'id_sala', 'id_sala');
    }

    // Relación con la fecha
    public function fechaRelacion()
    {
        return $this->belongsTo(Fecha::class, 'fecha', 'id');
    }

    public function horaRelacion()
    {
        return $this->belongsTo(Hora::class, 'hora', 'id');
    }

}