<?php

namespace App\Listeners;

use App\Events\SendMailVerificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendMailVerificationListener
{
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
    public function handle(SendMailVerificationEvent $event): void
    {
        $user = $event->user;
        $token = $user->verification_token;
        $verificationUrl = '/verify-email?token=$token';

        Mail::send('emails.verify-email', compact('verificationUrl', 'token'), function ($message) use ($user) {
            $message->to($user->email)->subject(__('common.lbl_subject_mail_verify'));
        });
    }
}
