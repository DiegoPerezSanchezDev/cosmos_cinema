<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CredencialesEmpleado extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $emailEmpleado;
    public $password;
    public $esAdmin;
    public $nombreAdminUsuario = null;
    public $codigoAdmin = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $nombre, string $emailEmpleado, string $password, bool $esAdmin, ?string $nombreAdminUsuario = null, ?string $codigoAdmin = null)
    {
        $this->nombre = $nombre;
        $this->emailEmpleado = $emailEmpleado;
        $this->password = $password;
        $this->esAdmin = $esAdmin;
        $this->nombreAdminUsuario = $nombreAdminUsuario;
        $this->codigoAdmin = $codigoAdmin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Tus Credenciales de Acceso a Cosmos')
                    ->markdown('emails.credenciales-empleado');
    }
}