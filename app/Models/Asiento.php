<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asiento extends Model
{
    use HasFactory;

    protected $table = 'asiento';
    protected $primaryKey = 'id_asiento';
    public $timestamps = true;

    protected $fillable = [
        'id_sesion_pelicula',
        'estado',
        'id_sala',
        'id_tipo_asiento',
        'columna',
        'fila',
    ];

    // Relación a tabla sesion_pelicula
    public function sesion_pelicula()
    {
        return $this->belongsTo(SesionPelicula::class, 'id_sesion_pelicula', 'id');
    }

    // Relación a tabla asiento_estado
    public function asiento_estado()
    {
        return $this->belongsTo(AsientoEstado::class, 'estado', 'id');
    }

    // Relación a tabla sala
    public function sala()
    {
        return $this->belongsTo(Sala::class, 'id_sala', 'id_sala');
    }

    // Relación a tabla tipo_asiento
    public function tipo_asiento()
    {
        return $this->belongsTo(TipoAsiento::class, 'id_tipo_asiento', 'id');
    }

}
