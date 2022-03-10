<?php
namespace Diagro\API;

use Closure;
use Exception;
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
abstract class API
{


    protected static closure $failHandler;

    protected static array $cached = [];


    public function __construct(protected Response $response)
    {
    }


    public static function withFail(closure $failHandler)
    {
        self::$failHandler = $failHandler;
    }


    public static function defaultFail()
    {
        self::$failHandler = function($response) { };
    }


    public static function getFailHandler(): closure
    {
        if(empty(self::$failHandler)) {
            self::defaultFail();
        }

        return self::$failHandler;
    }


    private static function getToken(): string
    {
        $token = request()->bearerToken();
        if(empty($token)) { //look in cookies
            $cookies = Arr::where(Cookie::getQueuedCookies(), function(\Symfony\Component\HttpFoundation\Cookie $cookie) {
                return $cookie->getName() == 'aat' && ! $cookie->isCleared();
            });

            if(count($cookies) == 1) {
                $token = $cookies[0]->getValue();
            } elseif(request()->hasCookie('aat')) {
                $token = request()->cookie('aat');
            } else {
                throw new Exception("No bearer token found to send with the request!");
            }
        }

        return $token;
    }


    private static function makeHeaders(array $headers = []): array
    {
        $token = self::getToken();
        $app_id = request()->header('x-app-id') ?? config('diagro.app_id');
        $defaultHeaders = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
            'x-app-id' => $app_id,
        ];

        return array_merge($headers, $defaultHeaders);
    }


    private static function makeHttp(array $headers): PendingRequest
    {
        return Http::withHeaders(self::makeHeaders($headers))->timeout(5);
    }


    private static function performHttp(string $method, string $path, array $data = [], array $headers = []): Response
    {
        if(! in_array($method, ['get', 'put', 'post', 'delete'])) {
            throw new \InvalidArgumentException("The method $method is not allowed for API requests!");
        }

        return self::makeHttp($headers)
            ->$method(static::url($path), $data)
            ->onError(self::getFailHandler());
    }


    public static function get(string $path, array $headers = [], array $query = []): static
    {
        return new static(self::performHttp('get', $path, $query, $headers));
    }


    public static function post(string $path, array $data, array $headers = []): static
    {
        return new static(self::performHttp('post', $path, $data, $headers));
    }


    public static function put(string $path, array $data, array $headers = []): static
    {
        return new static(self::performHttp('put', $path, $data, $headers));
    }


    public static function delete(string $path, array $data, array $headers = []): static
    {
        return new static(self::performHttp('delete', $path, $data, $headers));
    }


    /**
     * $cache_key example: self::concatToString(__FUNCTION__, $id)
     * The classname is already prefixed on the cache key.
     * If cache level is user, then key is prefixed with 'user_{user id}'
     * If cache level is company, then key is prefixed with 'company_{company id}'
     *
     * @param string $cache_key
     * @param Closure $closure
     * @param int $ttl
     * @param CacheLevel $cacheLevel
     * @return mixed
     */
    protected static function cache(string $cache_key, closure $closure, int $ttl = 3600, CacheLevel $cacheLevel = CacheLevel::USER)
    {
        $key = self::concatToString(
            str_replace('diagro_api_', '', strtolower(str_replace('\\', '_', static::class))),
            $cache_key
        );
        $tags = [$key];

        switch($cacheLevel)
        {
            case CacheLevel::USER:
                $key = 'user_' . auth()->user()->id() . '_' . $key;
                $tags[] = 'user_' . auth()->user()->id();
                break;
            case CacheLevel::COMPANY:
                $key = 'company_' . auth()->user()->company()->id() . '_' . $key;
                $tags[] = 'company_' . auth()->user()->company()->id();
                break;
        }

        if(! isset(self::$cached[$key])) {
            self::$cached[$key] = Cache::tags($tags)->remember($key, $ttl, $closure);
        }

        return self::$cached[$key];
    }


    protected static function concatToString(...$args)
    {
        return implode('_', $args);
    }


    public function json(?string $key = 'data'): array
    {
        return $this->response->json($key);
    }


    /**
     * Geeft de URL terug voor de requests.
     *
     * @param string $path the url path
     * @return string
     */
    protected static function url(string $path): string
    {
        throw new RuntimeException("Unimplemented!");
    }


}