<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\AlertaDiretor;

class AlertaDiretorMail extends Mailable
{
    use Queueable, SerializesModels;

    public AlertaDiretor $alerta;

    public function __construct(AlertaDiretor $alerta)
    {
        $this->alerta = $alerta;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIGEEX] ' . $this->alerta->titulo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alerta-diretor',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
