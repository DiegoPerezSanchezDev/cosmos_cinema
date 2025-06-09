<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AsientoEstado extends Model
{
    use HasFactory;

    protected $table = 'asiento_estado';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'estado',
    ];
}
