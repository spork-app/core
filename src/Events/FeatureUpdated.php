<?php

namespace Spork\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spork\Core\Contracts\HasFeatureListInterface;
use Spork\Core\Models\FeatureList;

class FeatureUpdated implements ShouldBroadcast, HasFeatureListInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public FeatureList $featureList)
    {
    }

    public function getFeatureList(): FeatureList
    {
        return $this->featureList;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.'.auth()->id());
    }
}
