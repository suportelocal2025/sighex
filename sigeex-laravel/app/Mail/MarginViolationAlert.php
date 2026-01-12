<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarginViolationAlert extends Mailable
{
    use Queueable, SerializesModels;

    public array $alertas;
    public int $ano;

    public function __construct(array $alertas, int $ano)
    {
        $this->alertas = $alertas;
        $this->ano = $ano;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SIGEEX - Alerta: Margem Orçamentária Ultrapassada',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.margin-violation',
            with: [
                'alertas' => $this->alertas,
                'ano' => $this->ano,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
