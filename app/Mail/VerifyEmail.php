<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifica tu correo electrónico',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify_email', // La vista del correo
            with: [
                'verificationUrl' => route('verification.verify', ['token' => $this->user->email_verification_token]), // <--- Aquí
                'user' => $this->user,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}