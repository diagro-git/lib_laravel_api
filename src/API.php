<?php
namespace Diagro\API;

use Closure;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;


/**
 * Helpers to call API backends
 *
 * @package Diagro\API\
 */
class API
{


    protected static closure $failHandler;


    public function __construct(protected EndpointDefinition $definition)
    {}


    public static function sync(EndpointDefinition $definition): array
    {
        $api = new self($definition);
        return $api->{$definition->method->value}();
    }


    public static function backend(EndpointDefinition $definition): array
    {
        $definition->addHeader('x-backend-token', env('DIAGRO_BACKEND_TOKEN'));
        return self::sync($definition);
    }


    public static function withFail(closure $failHandler)
    {
        self::$failHandler = $failHandler;
    }


    public static function defaultFail(): closure
    {
        return function($response) { };
    }


    public static function getFailHandler(): closure
    {
        if(empty(self::$failHandler)) {
            return self::defaultFail();
        }

        return self::$failHandler;
    }


    private function makeHeaders(): array
    {
        $defaultHeaders = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->definition->bearer_token,
            'x-app-id' => $this->definition->app_id,
        ];

        if(count($this->definition->fields) > 0) {
            $defaultHeaders['x-fields'] = implode(',', $this->definition->fields);
        }

        $request = request();
        $refs = $request->header('x-diagro-cache-refs', '');
        if($request->hasHeader('x-diagro-cache-key') && $request->hasHeader('x-diagro-cache-tags')) {
            if(! empty($refs)) {
                $refs .= ';';
            }
            $refs .= $request->header('x-diagro-cache-key') . ':' . $request->header('x-diagro-cache-tags');
            $defaultHeaders['x-diagro-cache-refs'] = $refs;
        }

        return array_merge($defaultHeaders, $this->definition->headers);
    }


    private function makeHttp(): PendingRequest
    {
        return Http::withHeaders($this->makeHeaders())->timeout($this->definition->timeout);
    }


    public function perform(): Response
    {
        $method = $this->definition->method->value;
        return $this->makeHttp()
            ->$method($this->definition->url, ($method == RequestMethod::GET->value ? $this->definition->query : $this->definition->data))
            ->onError(self::getFailHandler());
    }


    public function get(): array
    {
        $key = $this->definition->getCacheKey();
        if(! empty( $key) && ! empty( $this->definition->cache_tags)) {
            if(! $this->definition->hasHeader('x-diagro-cache-key')) {
                $this->definition->addHeader('x-diagro-cache-key', $this->definition->getCacheKey());
            }
            if(! $this->definition->hasHeader('x-diagro-cache-tags')) {
                $this->definition->addHeader('x-diagro-cache-tags', implode(',', $this->definition->cache_tags));
            }
        }

        //perform the get request
        return $this->perform()->json($this->definition->json_key) ?? [];
    }


    public function post(): array
    {
        $response = $this->perform();
        $json = $response->json($this->definition->json_key);
        if($json == null) {
            $json = ['body' => $response->body()];
        }
        return $json;
    }


    public function put(): array
    {
        $response = $this->perform();
        $json = $response->json($this->definition->json_key);
        if($json == null) {
            $json = ['body' => $response->body()];
        }
        return $json;
    }


    public function delete(): array
    {
        $response = $this->perform();
        $json = $response->json($this->definition->json_key);
        if($json == null) {
            $json = ['body' => $response->body()];
        }
        return $json;
    }


}