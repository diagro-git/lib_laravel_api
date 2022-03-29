<?php
namespace Diagro\API\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ResultMessage implements ShouldBroadcast
{

    use SerializesModels;


    public function __construct(public array $data)
    {
    }

    public function broadcastAs(): string
    {

    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('api-result');
    }

}
