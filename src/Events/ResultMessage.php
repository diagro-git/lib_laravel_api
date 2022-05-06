<?php
namespace Diagro\API\Events;

use Diagro\Events\CompanyUserBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ResultMessage extends CompanyUserBroadcast implements ShouldBroadcast
{

    use SerializesModels;


    public function __construct(private string $identifier, public array $data)
    {
        parent::__construct();
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
