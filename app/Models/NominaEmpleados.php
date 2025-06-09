<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // Asegúrate de importar esto
use Carbon\Carbon; // Para formatear el mes si lo necesitas más elaborado

class NominaEmpleados extends Model // SUGERENCIA: Nombre singular para el modelo
{
    use HasFactory;

    protected $table = 'nominaEmpleado'; // Coincide con tu migración

    protected $fillable = [
        'id_empleado',
        'mes',
        'anio',
        'generacion_fecha',
        'salario_bruto',
        'deducciones_seguridad_social',
        'irpf',
        'otras_deducciones',
        'salario_neto',
        'ruta_pdf',
    ];

    // Casts para los atributos
    protected $casts = [
        'generacion_fecha' => 'date',
        // 'salario_bruto' => 'decimal:2', // Opcional, pero útil para asegurar el tipo
        // 'deducciones_seguridad_social' => 'decimal:2',
        // 'irpf' => 'decimal:2',
        // 'otras_deducciones' => 'decimal:2',
        // 'salario_neto' => 'decimal:2',
    ];

    // Relación: Una nómina pertenece a un empleado (User)
    public function empleado()
    {
        return $this->belongsTo(User::class, 'id_empleado', 'id');
    }

    // Accesor para obtener el período formateado (MM/YYYY)
    protected function periodoCompleto(): Attribute
    {
        return Attribute::make(
            get: fn () => sprintf("%02d/%d", $this->mes, $this->anio),
        );
    }

    // Accesor para obtener el nombre completo del período (ej: "Enero 2023")
    // Si prefieres este formato, úsalo en lugar del anterior o crea uno nuevo
    protected function periodoNombreCompleto(): Attribute
    {
        return Attribute::make(
            // get: fn () => Carbon::createFromDate($this->anio, $this->mes, 1)->translatedFormat('F Y') // Necesita paquete de idioma
            get: fn () => Carbon::createFromDate($this->anio, $this->mes, 1)->monthName . ' ' . $this->anio
        );
    }


    // Accesor para obtener el salario neto formateado como moneda
    protected function salarioNetoFormateado(): Attribute // Cambiado a la nueva sintaxis
    {
        return Attribute::make(
            get: fn () => number_format($this->salario_neto, 2, ',', '.') . ' €',
        );
    }

    // Accesor para el total de deducciones
    protected function totalDeducciones(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->deducciones_seguridad_social + $this->irpf + $this->otras_deducciones,
        );
    }
}