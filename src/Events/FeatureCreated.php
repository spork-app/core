<?php

namespace Spork\Core\Events;

use Spork\Core\Models\FeatureList;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeatureCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public FeatureList $featureList)
    {
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.'.auth()->id());
    }
}
