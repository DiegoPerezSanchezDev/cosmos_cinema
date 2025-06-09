<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entrada extends Model
{
    use HasFactory;

    protected $table = 'entrada';
    protected $primaryKey = 'id_entrada';
    public $timestamps = true;

    protected $fillable = [
        'codigo_qr',
        'ruta_pdf',
        'estado',
        'precio_total',
        'descuento',
        'precio_final',
        'sala',
        'sala_id',
        'poster_ruta',
        'pelicula_titulo',
        'pelicula_id',
        'hora',
        'fecha',
        'asiento_id',
        'asiento_fila',
        'asiento_columna',
        'usuario_id',
        'factura_id',
        'tipo_entrada',
    ];

    // Relación a tabla sala
    public function sala()
    {
        return $this->belongsTo(Sala::class, 'id_sala', 'id_sala');
    }

    // Relación a tabla película
    public function pelicula()
    {
        return $this->belongsTo(Pelicula::class, 'pelicula_id', 'id');
    }

    // Relación a tabla asiento
    public function asiento()
    {
        return $this->belongsTo(Asiento::class, 'asiento_id', 'id_asiento');
    }

    // Relación a tabla user
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    // Relación a tabla factura
    public function factura()
    {
        return $this->belongsTo(Factura::class, 'id_factura', 'factura_id');
    }

     // Relación a tabla tipo_entrada
    public function tipoEntrada()
    {
        return $this->belongsTo(TipoEntrada::class, 'tipo_entrada', 'id_tipo_entrada');
    }

    public function salaEntrada() { // Nombre diferente a 'sala' para evitar conflicto con el campo 'sala'
        return $this->belongsTo(Sala::class, 'sala_id', 'id_sala');
    }
}
