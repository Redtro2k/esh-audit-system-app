<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class SendObservation extends Mailable
{
    use Queueable, SerializesModels;

    public $observation;

    public function __construct($observation)
    {
        $this->observation = $observation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Observation Submitted',
        );
    }
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.send-observation',
        );
    }
}
