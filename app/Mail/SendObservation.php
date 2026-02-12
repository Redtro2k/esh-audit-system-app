<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class SendObservation extends Mailable implements ShouldQueue
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
