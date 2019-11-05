<?php


namespace App\Listeners\Auth;

use App\Events\Auth\Invitation\Claimed;
use App\Events\Auth\Invitation\Forge;
use App\Mail\Auth\InvitationMail;
use App\Models\Actors\User;
use League\Event\Event;

class InvitationEventSubscriber
{
    /**
     * Send an email to recipient with invite link
     * @param Forge $event
     */
    public function handleForge(Forge $event)
    {
        \Mail::queue(new InvitationMail($event->invite));
    }

    /**
     * Make friend recipient and invite sender
     * Also set Invitation state to claimed
     * @param Claimed $event
     */
    public function handleClaimed(Claimed $event)
    {
        $event->invite->claim();
        /**
         * Destructuring
         * @see https://www.php.net/manual/en/migration71.new-features.php#migration71.new-features.symmetric-array-destructuring
         */
        /** @var User $user */
        /** @var User $recipient */
        [$user, $recipient] = [$event->invite->sender, User::whereEmail($event->invite->email)->firstOrFail()];

        $user->befriend($recipient);
        $recipient->acceptFriendRequest($user);
    }

}
