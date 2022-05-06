<?php
namespace Diagro\API\Jobs;

use Diagro\API\API;
use Diagro\API\EndpointDefinition;
use Diagro\API\Events\ResultMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class AsyncRequest implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable;


    private $company_id;

    private $user_id;


    public function __construct(
        public EndpointDefinition $definition,
        public string $identifier
    )
    {
        $this->company_id = auth()->user()->company()->id();
        $this->user_id = auth()->user()->id();

        $this->onQueue('async');
    }


    public function handle()
    {
        $api = new API($this->definition);
        $result = $api->{$this->definition->method->value}();

        //send out the message to the websocket through eventbus
        event(new ResultMessage($this->identifier, $result, $this->company_id, $this->user_id));
    }

}