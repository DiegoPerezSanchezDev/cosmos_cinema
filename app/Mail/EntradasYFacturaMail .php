<?php 

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EntradasYFacturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $mail = $this->subject('Tus Entradas y Factura de Cosmos Cinema')
                     ->view('emails.entradas_y_factura'); // Nombre de la vista Blade

        // Adjuntar las entradas
        if (isset($this->data['pdfEntradas']) && is_array($this->data['pdfEntradas'])) {
            foreach ($this->data['pdfEntradas'] as $pdfEntrada) {
                $mail->attachData($pdfEntrada['content'], $pdfEntrada['filename'], ['mime' => 'application/pdf']);
            }
        }

        // Adjuntar la factura
        if (isset($this->data['pdfFactura'])) {
            $mail->attachData($this->data['pdfFactura']['content'], $this->data['pdfFactura']['filename'], ['mime' => 'application/pdf']);
        }

        return $mail;
    }
}