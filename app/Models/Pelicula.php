<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pelicula extends Model
{
    use HasFactory;

    protected $table = 'pelicula';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'adult',
        'backdrop_ruta',
        'id_api',
        'lenguaje_original',
        'titulo_original',
        'sinopsis',
        'poster_ruta',
        'fecha_estreno',
        'titulo',
        'video',
        'id_sala',
        'activa',
        'duracion',
        'puntuacion_promedio',
        'numero_votos',
        'popularidad',
        'estreno' => 'boolean',
    ];

    protected $casts = [
        'adult' => 'boolean',
        'video' => 'boolean',
        'created_at' => 'datetime',
        'activa' => 'boolean',
        'estreno' => 'boolean', 
    ];

    public function generos(): BelongsToMany
    {
        return $this->belongsToMany(
            GeneroPelicula::class,      // Modelo relacionado
            'pelicula_genero',          // Nombre de la tabla pivote
            'id_pelicula',              // Clave foránea de este modelo (Pelicula) en la tabla pivote
            'id_genero_pelicula'        // Clave foránea del modelo relacionado (GeneroPelicula) en la tabla pivote
        );
    }

    public function sesiones()
{
    return $this->hasMany(Sesion::class, 'id_pelicula');
}
}
