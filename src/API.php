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


    public static function async(EndpointDefinition $definition, string $identifier): array
    {
        AsyncRequest::dispatch($definition, $identifier)->afterResponse();
        return [];
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
        if($request->hasHeader('x-diagro-cache')) {
            $defaultHeaders['x-diagro-cache'] = $request->header('x-diagro-cache');
        } elseif(count($this->definition->cache_tags) > 0 && ! empty( $this->definition->cache_key)) {
            $defaultHeaders['x-diagro-cache'] = $this->definition->cache_key . ';' . implode(' ', $this->definition->cache_tags);
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
        if(! isset(self::$cached[$key]) || empty(self::$cached[$key])) {
            //Send the tags and key to the backend.
            $this->definition->addHeader('X-Diagro-Cache', sprintf('%s;%s', $key, implode(' ', $this->definition->cache_tags)));
            self::$cached[$key] = Cache::tags($this->definition->cache_tags)->remember($key, $this->definition->cache_ttl, fn() => $this->perform()->json($this->definition->json_key));
        }

        return self::$cached[$key];
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