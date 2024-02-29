<?php

namespace App\Providers;

use App\Events\SendMailCodeForgotPasswordEvent;
use App\Events\SendMailVerificationEvent;
use App\Listeners\SendMailCodeForgotPasswordListener;
use App\Listeners\SendMailVerificationListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Event::listen(
            SendMailVerificationEvent::class,
            SendMailVerificationListener::class,
        );

        Event::listen(
            SendMailCodeForgotPasswordEvent::class,
            SendMailCodeForgotPasswordListener::class,
        );
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
