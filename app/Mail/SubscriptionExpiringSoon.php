<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SubscriptionExpiringSoon extends Mailable
{
    public function __construct(
        public User $user,
        public int $daysRemaining
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your subscription expires in {$this->daysRemaining} days",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription-expiring-soon',
            with: [
                'user' => $this->user,
                'daysRemaining' => $this->daysRemaining,
                'expiresAt' => $this->user->subscription_expires_at,
            ],
        );
    }
}