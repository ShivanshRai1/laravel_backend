<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\OnboardingMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOnboardingEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        // Send onboarding email to newly registered user
        Mail::to($event->user->email)->send(new OnboardingMail($event->user));
    }
}
