<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    use HasFactory;

    protected $table = 'descuento';
    protected $primaryKey = 'id_descuento';
    public $timestamps = false;

    protected $fillable = [
        'descuento',
        'tipo'
    ];
}
