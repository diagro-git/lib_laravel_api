<?php
namespace Diagro\API;

use Closure;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;


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


    private static function makeHeaders(array $headers = []): array
    {
        $defaultHeaders = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . request()->bearerToken(),
            'x-app-id' => request()->header('x-app-id'),
        ];

        return array_merge($headers, $defaultHeaders);
    }


    private static function makeHttp(array $headers): PendingRequest
    {
        return Http::withHeaders(self::makeHeaders($headers))->timeout(5);
    }


    public static function get(array $headers = [], array $query = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->get(static::url(), $query)
            ->onError(self::getFailHandler());
    }


    public static function post(array $data, array $headers = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->post(static::url(), $data)
            ->onError(self::getFailHandler());
    }


    public static function put(array $data, array $headers = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->put(static::url(), $data)
            ->onError(self::getFailHandler());
    }


    public static function delete(array $data, array $headers = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->delete(static::url(), $data)
            ->onError(self::getFailHandler());
    }


    /**
     * Geeft de URL terug voor de requests.
     *
     * @return string
     */
    abstract static protected function url(): string;


}