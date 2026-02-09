<?php

namespace App\Mail;

use App\Models\Alert;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AlertNotification extends Mailable
{
    public function __construct(
        public Alert $alert,
        public string $message
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠️ Alert: {$this->alert->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alert-notification',
            with: [
                'alert' => $this->alert,
                'message' => $this->message,
                'projectName' => $this->alert->project->name,
            ],
        );
    }
}
