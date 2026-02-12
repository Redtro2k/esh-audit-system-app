<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForResolved extends Mailable implements ShouldQueue
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
            subject: 'Resolved Observation Issue',
        );
    }
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.resolved',
        );
    }
}
