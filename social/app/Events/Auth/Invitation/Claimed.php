<?php

namespace App\Events\Auth\Invitation;

use App\Models\Email\Invitation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Claimed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invite;
    /**
     * Create a new event instance.
     *
     * @param Invitation $invite
     */
    public function __construct(Invitation $invite)
    {
        $this->invite = $invite;
    }

}
