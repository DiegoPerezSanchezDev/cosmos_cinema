<?php

namespace App\Models; // ****** IMPORTANTE: Asegúrate de que este namespace coincide con la carpeta real de tus modelos (normalmente App\Models) ******

// Importa la clase base User de Laravel para hacerlo autenticable
use Illuminate\Foundation\Auth\User as Authenticatable;
// Importa los traits necesarios
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable; // Incluir si planeas enviar notificaciones a admins


// ****** TU MODELO DE ADMINISTRADOR: Extiende la clase base User de Laravel ******
class Administrator extends Authenticatable // Al extender Authenticatable (alias de User), hereda métodos para autenticación
{
    use HasFactory, Notifiable;

    protected $table = 'administrador'; 

    protected $primaryKey = 'id_administrador';


    protected $fillable = [
        'nombre_user_admin',             
        'email',           
        'codigo_administrador',
    ];

    // Define qué atributos deben ser ocultados (no incluidos) al convertir el modelo a array o JSON (ej: para APIs)
    protected $hidden = [          
        'codigo_administrador',
    ];

    public function getAuthIdentifierName(): string
    {
        return $this->primaryKey;
    }

}    