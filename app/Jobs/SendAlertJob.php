<?php

namespace App\Jobs;

use App\Models\Alert;
use App\Models\Project;
use App\Mail\AlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendAlertJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Alert $alert,
        public Project $project
    ) {}

    public function handle(): void
    {
        $message = "Test alert from project: {$this->project->name}";

        foreach ($this->alert->recipients as $email) {
            Mail::to($email)->send(new AlertNotification($this->alert, $message));
        }
    }
}
