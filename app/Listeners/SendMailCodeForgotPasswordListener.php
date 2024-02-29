<?php

namespace App\Listeners;

use App\Events\SendMailCodeForgotPasswordEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendMailCodeForgotPasswordListener
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
    public function handle(SendMailCodeForgotPasswordEvent $event): void
    {
        $data = $event->data;
        $user = $event->user;
        $code = $data['code'];

        Mail::send('emails.forgot-email', compact('code'), function ($message) use ($user) {
            $message->to($user->email)->subject(__('common.lbl_subject_mail_forgot'));
        });
    }
}
