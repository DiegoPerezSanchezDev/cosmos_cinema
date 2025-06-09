<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GeneroPelicula extends Model
{
    use HasFactory;

    protected $table = 'genero_pelicula';
    protected $primaryKey = 'id_genero_pelicula';
    public $timestamps = false;

    protected $fillable = [
        'genero',
    ];

    public function peliculas(): BelongsToMany
    {
        return $this->belongsToMany(
            Pelicula::class,            // Modelo relacionado
            'pelicula_genero',          // Nombre de la tabla pivote
            'id_genero_pelicula',       // Clave foránea de este modelo (GeneroPelicula) en la tabla pivote
            'id_pelicula'               // Clave foránea del modelo relacionado (Pelicula) en la tabla pivote
        );
    }
}