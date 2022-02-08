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


    public static function get(string $url, array $headers = [], array $query = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->get($url, $query)
            ->onError(self::getFailHandler());
    }


    public static function post(string $url, array $data, array $headers = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->post($url, $data)
            ->onError(self::getFailHandler());
    }


    public static function put(string $url, array $data, array $headers = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->put($url, $data)
            ->onError(self::getFailHandler());
    }


    public static function delete(string $url, array $data, array $headers = []): \Illuminate\Http\Client\Response
    {
        return self::makeHttp($headers)
            ->delete($url, $data)
            ->onError(self::getFailHandler());
    }


}