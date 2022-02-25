<?php
namespace Diagro\API;

use Closure;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
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
        $token = request()->bearerToken() ?? request()->cookie('aat');
        if(empty($token)) {
            $cookies = Arr::where(Cookie::getQueuedCookies(), function(\Symfony\Component\HttpFoundation\Cookie $cookie) {
                return $cookie->getName() == 'aat';
            });

            if(count($cookies) == 1) {
                $token = $cookies[0];
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


    public static function get(string $path, array $headers = [], array $query = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->get(static::url($path), $query)
            ->onError(self::getFailHandler());
    }


    public static function post(string $path, array $data, array $headers = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->post(static::url($path), $data)
            ->onError(self::getFailHandler());
    }


    public static function put(string $path, array $data, array $headers = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->put(static::url($path), $data)
            ->onError(self::getFailHandler());
    }


    public static function delete(string $path, array $data, array $headers = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->delete(static::url($path), $data)
            ->onError(self::getFailHandler());
    }


    /**
     * Geeft de URL terug voor de requests.
     *
     * @param string $path the url path
     * @return string
     */
    static protected function url(string $path): string
    {
        throw new RuntimeException("Unimplemented!");
    }


}