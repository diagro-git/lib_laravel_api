<?php
namespace Diagro\API;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;

/**
 * Use this trait in the class where the endpoints are defined.
 */
trait ApiEndpoints
{


    /**
     * Construct the complete URL with given path.
     *
     * @param string $path
     * @return string
     */
    abstract protected function url(string $path): string;


    /**
     * Get an endpoint definition instance for given path and request method.
     * The follow properties in the definition are set:
     *
     *  - identifier = the application_section cache key
     *  - url
     *  - method
     *  - bearer_token
     *  - app_id
     *  - timeout
     *  - cache_ttl
     *
     * If the request method is GET, the cache_tags are set for user, company and application_section
     * If the request is not GET, the applcation_section tag is set for delete after a successfull request.
     *
     * @param string $path
     * @param RequestMethod $method
     * @return EndpointDefinition
     * @throws Exception
     */
    protected function factoryDefinition(string $path, RequestMethod $method): EndpointDefinition
    {
        $definition = (new EndpointDefinition($this->applicationSectionCacheKey(), $this->url($path), $method, $this->getToken(), $this->getAppId()))
            ->setTimeout(env('DIAGRO_API_REQUEST_TIMEOUT', 30))
            ->setCacheTTL(env('DIAGRO_API_CACHE_TTL', 3600));

        if($method == RequestMethod::GET) {
            $definition->setCacheTags($this->getCacheTags());
        } else {
            $definition->addCacheTagDelete($this->applicationSectionCacheKey());
        }

        return $definition;
    }


    /**
     * Get the app id based on the x-app-id header or the diagro.app_id config value.
     *
     * @return string
     */
    protected function getAppId(): string
    {
        return request()->header('x-app-id') ?? config('diagro.app_id');
    }


    /**
     * Get the bearer token from auth request header or from cookie.
     * If no token is found, an exception is thrown.
     *
     * @return string
     * @throws Exception
     */
    protected function getToken(): string
    {
        $token = request()->bearerToken();
        if (empty($token)) { //look in cookies
            $cookies = Arr::where(Cookie::getQueuedCookies(), function (\Symfony\Component\HttpFoundation\Cookie $cookie) {
                return $cookie->getName() == 'aat' && !$cookie->isCleared();
            });

            if (count($cookies) == 1) {
                $token = $cookies[0]->getValue();
            } elseif (request()->hasCookie('aat')) {
                $token = request()->cookie('aat');
            } else {
                throw new Exception("No bearer token found to send with the request!");
            }
        }

        return $token;
    }


    /**
     * Get the default cache tags: user, company and application_section.
     *
     * @return array
     */
    protected function getCacheTags(): array
    {
        $tags = [];
        $tags[] = "user_" . request()->user()->id();
        $tags[] = "company_" . request()->user()->company()->id();
        $tags[] = $this->applicationSectionCacheKey();

        return $tags;
    }


    /**
     * Get the application_section cache key. If no class is used, the classname is used where this trait is used in.
     *
     * @param string|null $class_name
     * @return string
     */
    protected function applicationSectionCacheKey(?string $class_name = null): string
    {
        if(empty($class_name) || ! class_exists($class_name)) {
            $class_name = static::class;
        }

        return str_replace('diagro_api_', '', strtolower(str_replace('\\', '_', $class_name)));
    }


}