<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impuesto extends Model
{
    use HasFactory;

    protected $table = 'impuesto';
    protected $primaryKey = 'id_impuesto';

    /**
     * Indicates if the model should be timestamped.
     * Tu migración de impuesto no define timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tipo',
        'cantidad', // ej: 21.00 para el 21%
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad' => 'float',
    ];

    /**
     * Accessor to get the tax rate as a usable decimal value.
     * (e.g., if 'cantidad' is 21.00, this returns 0.21)
     *
     * @return float
     */
    public function getTasaCalculableAttribute(): float
    {
        // Accede a 'cantidad' a través del atributo casteado si es posible
        if (isset($this->cantidad)) {
            return (float) ($this->cantidad / 100);
        }
        return 0.0; // Default to 0 if 'cantidad' is not set, to prevent errors
    }

    /**
     * Get the facturas associated with the impuesto.
     * (Inverse relationship - optional but can be useful)
     */
    public function facturas()
    {
        return $this->hasMany(Factura::class, 'id_impuesto', 'id_impuesto');
    }
}