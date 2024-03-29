<?php

namespace App\Listeners;

use App\Events\UserHasRunOutOfCredits;
use App\Notifications\NotEnoughCreditsNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendNotEnoughCreditsNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserHasRunOutOfCredits  $event
     * @return void
     */
    public function handle(UserHasRunOutOfCredits $event)
    {
        $event->user->notify(new NotEnoughCreditsNotification($event->user));
    }
}
