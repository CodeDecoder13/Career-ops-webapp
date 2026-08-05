<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

final class NewJobsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, Application>  $newApplications
     */
    public function __construct(
        public readonly Collection $newApplications,
    ) {}

    public function envelope(): Envelope
    {
        $count = $this->newApplications->count();

        return new Envelope(
            subject: $count === 1
                ? '1 new job added to career-ops-tracker'
                : "{$count} new jobs added to career-ops-tracker",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-jobs',
            with: [
                'applications' => $this->newApplications,
            ],
        );
    }
}
