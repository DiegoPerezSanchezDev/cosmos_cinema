<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'menus';
    protected $primaryKey = 'id';
   /*  public $timestamps = false; */

    // Define los atributos que pueden ser asignados masivamente (fillable)
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'foto_ruta',
        'activo',
    ];

    // Define los atributos que deben ser casteados a tipos nativos
    protected $casts = [
        'activo' => 'boolean',
        'precio' => 'decimal:2',
    ];

    
}
