<?php
namespace Diagro\API\Jobs;

use Diagro\API\API;
use Diagro\API\EndpointDefinition;
use Diagro\API\Events\ResultMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Pusher\Pusher;

class AsyncRequest implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable;


    private int $user_id;

    private int $company_id;

    public function __construct(
        public EndpointDefinition $definition,
        public string $identifier
    )
    {
        $this->user_id = auth()->user()->id();
        $this->company_id = auth()->user()->company()->id();
    }


    public function handle()
    {
        $api = new API($this->definition);
        $result = $api->{$this->definition->method->value}();

        //send out the message to the websocket through eventbus
        $attemps = 0;
        while(! $this->hasUsers() && $attemps < 5) {
            usleep(500*1000);
            $attemps++;
        }
        if($attemps < 5) {
            event(new ResultMessage($this->identifier, $result, $this->user_id, $this->company_id));
        } else {
            logger()->error("Attemps 5 reached!");
        }
    }

    private function hasUsers(): bool
    {
        $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), ['cluster' => env('PUSHER_APP_CLUSTER')]);
        $info = $pusher->getChannelInfo('private-Diagro.API.Async.' . $this->user_id . '.' . $this->company_id);
        return $info->occupied;
    }

}