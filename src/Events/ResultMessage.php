<?php
namespace Diagro\API\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ResultMessage implements ShouldBroadcast
{

    use SerializesModels;


    public $queue = 'events_result';


    public function __construct(private string $identifier, public array $data, private int $user_id, private int $company_id)
    {
    }

    public function broadcastAs(): string
    {
        return $this->identifier;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('Diagro.API.Async.' . $this->user_id . '.' . $this->company_id);
    }

}
