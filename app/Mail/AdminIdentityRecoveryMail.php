<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminIdentityRecoveryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public array $data, public string $ip)
    {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 Demande de Récupération d\'Identité Groupe ITES',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.admin-identity-recovery',
        );
    }
}
