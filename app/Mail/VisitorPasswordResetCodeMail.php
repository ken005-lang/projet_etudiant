<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VisitorPasswordResetCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $expiresIn;

    public function __construct(public User $user, public string $code)
    {
        $this->expiresIn = 2; // 2 minutes validity
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre code de réinitialisation de mot de passe',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.visitor-code',
        );
    }
}
