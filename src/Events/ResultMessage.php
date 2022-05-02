<?php
namespace Diagro\API\Events;

use Diagro\Events\BroadcastWhenOccupied;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ResultMessage implements ShouldBroadcast
{

    use SerializesModels, BroadcastWhenOccupied;


    public function __construct(private string $identifier, public array $data)
    {
        $this->user_id = auth()->user()->id();
    }

    public function broadcastAs(): string
    {
        return $this->identifier;
    }

    protected function channelName(): string
    {
        return 'Diagro.API.Async';
    }
}
