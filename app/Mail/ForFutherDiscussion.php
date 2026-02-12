<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForFutherDiscussion extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $observation;

    public function __construct($observation)
    {
        $this->observation = $observation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'For Futher Discussion',
        );
    }
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.for-further-discussion',
        );
    }
}
