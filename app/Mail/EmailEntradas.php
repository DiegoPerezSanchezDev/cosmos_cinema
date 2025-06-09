<?php

namespace App\Mail;

use App\Models\Factura;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Log;

class EmailEntradas extends Mailable
{
    use Queueable, SerializesModels;

    public User $usuario;
    public Factura $factura;
    public array $rutas_pdf;
    public ?string $rutaPdfFactura = null;

    // Crear instancia de Email
    public function __construct(User $usuario, Factura $factura, array $rutas_pdf, ?string $ruta_pdf_factura = null)
    {
        $this->usuario = $usuario;
        $this->factura = $factura;
        $this->rutas_pdf = $rutas_pdf;
        $this->rutaPdfFactura = $ruta_pdf_factura;
    }

    // Cabeceras ('sobre' del correo)
    // Recuperamos las constances de .env (correo y nombre)
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Confirmación de tu Compra en Cosmos Cinema',
        );
    }

    // Contenido
    // TODO -> Añadir número de factura al correo
    public function content(): Content
    {
        return new Content(
            markdown: 'email.entradas',    // Vista blade
            with: [
                'nombreUsuario' => $this->usuario->nombre,
                //'numeroFactura' => $this->factura->numero_factura,
                'urlSitio' => url('/'),
            ],
        );
    }

    // Adjuntos
    // TODO -> Generar vista de factura
    // TODO -> Adjuntar factura
    public function attachments(): array
    {
        $archivos_adjuntos = [];
        foreach ($this->rutas_pdf as $indice => $ruta_pdf) {
            Log::info($ruta_pdf);
            if (file_exists($ruta_pdf)) {
                $nombreArchivoOriginal = basename($ruta_pdf);
                $archivos_adjuntos[] = Attachment::fromPath($ruta_pdf)
                    ->as('Entrada-' . ($indice + 1) . '.pdf')
                    ->withMime('application/pdf');
            } else {
                Log::error("Archivo PDF de entrada no encontrado: " . $ruta_pdf);
            }
        }

        // Adjuntar la factura si la ruta está definida y el archivo existe
        if ($this->rutaPdfFactura && file_exists($this->rutaPdfFactura)) {
            Log::info($this->rutaPdfFactura);
            $archivos_adjuntos[] = Attachment::fromPath($this->rutaPdfFactura)
                ->as('Factura.pdf')
                ->withMime('application/pdf');
        } elseif ($this->rutaPdfFactura) {
            Log::error("Archivo PDF de factura no encontrado: " . $this->rutaPdfFactura);
        }

        return $archivos_adjuntos;
    }
}
