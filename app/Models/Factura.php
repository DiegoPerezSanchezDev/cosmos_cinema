<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// Asegúrate de que los namespaces de User e Impuesto sean correctos
use App\Models\User;
use App\Models\Impuesto;
use Carbon\Carbon; // Necesario para el casteo de fechas si no se hace automáticamente

class Factura extends Model
{
    use HasFactory;

    protected $table = 'factura';
    protected $primaryKey = 'id_factura';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true; // Tu migración tiene timestamps 'created_at' y 'updated_at'

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'monto_total',      // Este es la BASE IMPONIBLE (antes de impuestos)
        'titular_email',
        'num_factura',      // Id de pedido
        'pedido_redsys_id', // Pedido id para Redsys
        'codigo_autorizacion_redsys',   // Código de respuesta de Redsys
        'fecha_pago',       // Fecha en la que se realizó el pago
        'estado',           // Estado de la factura (Pendiente, Pagado, Cancelado)
        'id_user',          // Clave foránea para la tabla users
        'id_impuesto',      // Clave foránea para la tabla impuesto
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'monto_total' => 'float',
        'ultimos_digitos' => 'integer',
        'created_at' => 'datetime', // Asegura que Eloquent trate estas como objetos Carbon
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the factura.
     */
    public function user()
    {
        // Clave foránea 'id_user' en la tabla 'factura'
        // Clave primaria 'id' en la tabla 'users'
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /**
     * Get the impuesto associated with the factura.
     */
    public function impuesto()
    {
        // Clave foránea 'id_impuesto' en la tabla 'factura'
        // Clave primaria 'id_impuesto' en la tabla 'impuesto'
        return $this->belongsTo(Impuesto::class, 'id_impuesto', 'id_impuesto');
    }

    /**
     * Accessor for Monto Neto (Base Imponible).
     * 'monto_total' de la tabla factura YA ES el neto sin impuesto.
     *
     * @return float
     */
    public function getMontoNetoSinImpuestoAttribute(): float
    {
        return (float) $this->monto_total; // Accede a través del atributo casteado si es posible
    }

    /**
     * Accessor for the Monto del Impuesto.
     *
     * @return float
     */
    public function getMontoImpuestoAttribute(): float
    {
        // Verifica que la relación 'impuesto' esté cargada y no sea null
        // y que el accesor 'tasa_calculable' exista en el modelo Impuesto y devuelva un valor.
        if ($this->relationLoaded('impuesto') && $this->impuesto && isset($this->impuesto->tasa_calculable) && $this->impuesto->tasa_calculable != 0) {
            return $this->getMontoNetoSinImpuestoAttribute() * $this->impuesto->tasa_calculable;
        }
        return 0.0;
    }

    /**
     * Accessor for the Monto Bruto (Total a Pagar por el Cliente).
     *
     * @return float
     */
    public function getMontoBrutoConImpuestoAttribute(): float
    {
        return $this->getMontoNetoSinImpuestoAttribute() + $this->getMontoImpuestoAttribute();
    }

    /**
     * The accessors to append to the model's array form.
     * Esto asegura que estos campos se incluyan cuando el modelo se convierte a JSON/array,
     * lo cual es útil para las respuestas AJAX.
     *
     * @var array
     */
    protected $appends = [
        'monto_neto_sin_impuesto',
        'monto_impuesto',
        'monto_bruto_con_impuesto',
    ];
}