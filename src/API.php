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
class API
{


    protected static closure $failHandler;

    protected static array $cached = [];

    protected static array $headers = [];


    protected function __construct(protected ?Response $response = null)
    {
    }


    public static function withFail(closure $failHandler): static
    {
        self::$failHandler = $failHandler;
        return new static;
    }


    public static function defaultFail(): static
    {
        self::$failHandler = function($response) { };
        return new static;
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

        return array_merge($defaultHeaders, self::$headers, $headers);
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
     *
     * @param string $endpoint
     * @param Closure $closure
     * @param int|null $ttl
     * @return mixed
     */
    protected static function cache(string $endpoint, closure $closure, ?int $ttl = null)
    {
        $user = 'user_' . auth()->user()->id();
        $company = 'company_' . auth()->user()->company()->id();
        $application_section = self::applicationSectionCacheKey();
        $key = self::concatToString($user, $company, $application_section, $endpoint);
        $tags = [$user, $company, $application_section, $endpoint];

        //ttl
        if($ttl == null) {
            $ttl = env('DIAGRO_API_CACHE_TTL', 3600);
        }

        if(! isset(self::$cached[$key])) {
            self::$cached[$key] = Cache::tags($tags)->remember($key, $ttl, $closure);
        }

        return self::$cached[$key];
    }


    protected static function applicationSectionCacheKey(?string $class_name = null): string
    {
        if(empty($class_name) || ! class_exists($class_name)) {
            $class_name = static::class;
        }

        return str_replace('diagro_api_', '', strtolower(str_replace('\\', '_', $class_name)));
    }


    protected static function concatToString(...$args)
    {
        return implode('_', $args);
    }


    public function json(?string $key = 'data'): array
    {
        return $this->response->json($key);
    }


    public function deleteCache(?string $endpoint = null, ?string $class_name = null): static
    {
        $tags = [self::applicationSectionCacheKey($class_name)];
        if($endpoint != null) {
            $tags[] = $endpoint;
        }

        Cache::tags($tags)->flush();

        return $this;
    }


    public static function withHeaders(array $headers): static
    {
        self::$headers = $headers;
        return new static;
    }


    public static function fields(array $fields): static
    {
        self::$headers['x-fields'] = implode(',', $fields);
        return new static;
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