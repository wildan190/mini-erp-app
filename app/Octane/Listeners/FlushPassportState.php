<?php

namespace App\Octane\Listeners;

use Laravel\Octane\Events\RequestTerminated;
use Laravel\Passport\Passport;

/**
 * Clear any Passport state that could accumulate across requests in a
 * long-lived Swoole worker.
 *
 * Passport\Token models loaded during authentication are stored on the User
 * instance, which itself may survive across requests if the container binding
 * is shared. This listener ensures those references are released.
 */
class FlushPassportState
{
    public function handle(RequestTerminated $event): void
    {
        // Reset the current access-token reference on the authenticated user
        // so it is not carried over to the next request by the same worker.
        $user = $event->request->user('platform');

        if ($user && method_exists($user, 'withAccessToken')) {
            $user->withAccessToken(null);
        }
    }
}
