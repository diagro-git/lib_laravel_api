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


    public function __construct(
        public EndpointDefinition $definition
    )
    {
    }


    public function handle()
    {
        $api = new API($this->definition);
        $result = $api->{$this->definition->method->value}();

        //send out the message to the websocket through eventbus
        event(new ResultMessage($result));
    }

}