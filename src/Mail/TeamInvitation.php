<?php

namespace TeamStream\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TeamInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public mixed $invitation)
    {
        if (empty($invitation->token)) {
            $invitation->forceFill(['token' => Str::random(64)])->save();
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('You have been invited to join the :team team!', [
                'team' => $this->invitation->team->name,
            ])
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'teamstream::mail.team-invitation',
            with: [
                'acceptUrl' => url(route('team-invitations.accept', ['token' => $this->invitation->token])),
                'teamName' => $this->invitation->team->name,
            ],
        );
    }
}
