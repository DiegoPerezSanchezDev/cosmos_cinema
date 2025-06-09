<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoEntrada extends Model
{
    use HasFactory;

    protected $table = 'tipo_entrada';
    protected $primaryKey = 'id_tipo_entrada';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'precio',
    ];
}
