<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $resetUrl;
    public $expiresIn;

    public function __construct(public User $user, private string $rawToken, public string $mode)
    {
        $this->expiresIn = 60;
        $this->resetUrl = route('password.reset', [
            'token' => $this->rawToken,
            'email' => $this->user->email,
            'mode' => $this->mode
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mode === 'groupe' ? 'Récupération de votre Code ID' : 'Réinitialisation de votre mot de passe',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.password-reset',
        );
    }
}
