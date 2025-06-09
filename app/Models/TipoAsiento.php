<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoAsiento extends Model
{
    use HasFactory;

    protected $table = 'tipo_asiento';

    protected $fillable = [
        'tipo',
    ];
}
