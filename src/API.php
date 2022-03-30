<?php
namespace Diagro\API;

use Closure;
use Diagro\API\Jobs\AsyncRequest;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use RuntimeException;


/**
 * Helpers to call API backends
 *
 * @package Diagro\API\
 */
class API
{


    protected static closure $failHandler;

    protected static array $cached = [];


    public function __construct(protected EndpointDefinition $definition)
    {}


    public static function sync(EndpointDefinition $definition): array
    {
        $api = new self($definition);
        return $api->{$definition->method->value}();
    }


    public static function async(EndpointDefinition $definition): array
    {
        AsyncRequest::dispatch($definition);
        return [];
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
            ->$method($this->definition->url, ($method == RequestMethod::GET ? $this->definition->query : $this->definition->data))
            ->onError(self::getFailHandler());
    }


    public function get(): array
    {
        $key = $this->definition->getCacheKey();
        if(! isset(self::$cached[$key])) {
            self::$cached[$key] = Cache::tags($this->definition->cache_tags)->remember($key, $this->definition->cache_ttl, fn() => $this->perform()->json($this->definition->json_key));
        }

        return self::$cached[$key];
    }


    public function post(): array
    {
        $response = $this->perform();
        if($response->successful() && count($this->definition->cache_tags_delete)) {
            Cache::tags($this->definition->cache_tags_delete)->flush();
        }
        return $response->json($this->definition->json_key);
    }


    public function put(): array
    {
        $response = $this->perform();
        if($response->successful() && count($this->definition->cache_tags_delete)) {
            Cache::tags($this->definition->cache_tags_delete)->flush();
        }
        return $response->json($this->definition->json_key);
    }


    public function delete(): array
    {
        $response = $this->perform();
        if($response->successful() && count($this->definition->cache_tags_delete)) {
            Cache::tags($this->definition->cache_tags_delete)->flush();
        }
        return $response->json($this->definition->json_key);
    }


}