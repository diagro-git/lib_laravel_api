<?php
namespace Diagro\API;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;

class EndpointDefinition
{

    /**
     * The names of json fields that are used.
     *
     * @var array
     */
    public array $fields = [];

    /**
     * The data send with the request body.
     *
     * @var array
     */
    public array $data = [];

    /**
     * Query data for GET requests.
     *
     * @var array
     */
    public array $query = [];

    /**
     * Additional headers.
     *
     * @var array
     */
    public array $headers = [];

    /**
     * The logged in user object. This is uses for caching.
     *
     * @var Authenticatable|null
     */
    public ?Authenticatable $user = null;

    /**
     * The tags used for caching.
     *
     * @var array
     */
    public array $cache_tags = [];

    /**
     * The key identifier of the cache entry.
     *
     * @var string|null
     */
    public ?string $cache_key = null;

    /**
     * Time to live for the cache entry.
     * Default: 60 minutes.
     *
     * @var int
     */
    public int $cache_ttl = 3600;

    /**
     * Which cache entries with given cache tags are removed after
     * successfull request.
     *
     * @var array
     */
    public array $cache_tags_delete = [];

    /**
     * Request timeout in seconds.
     * Default: 30 seconds.
     *
     * @var int
     */
    public int $timeout = 30;

    /**
     * The name of the JSON key in the response.
     *
     * @var string
     */
    public string $json_key = 'data';


    public function __construct(
        public string $identifier,
        public string $url,
        public RequestMethod $method,
        public string $bearer_token,
        public int $app_id
    )
    {
        $this->updateEndpointCacheKey();
    }

    public function updateEndpointCacheKey(): void
    {
        $endpoint_cache_key = str_replace('/', '_', Arr::get(parse_url($this->url, PHP_URL_PATH), 'path', ''));
        //remove cache tag that starts with this part
        foreach($this->cache_tags as $idx => $tag) {
            if(str_contains($tag, $endpoint_cache_key)) {
                unset($this->cache_tags[$idx]);
            }
        }

        //concat the query part
        foreach($this->query as $k => $v) {
            $endpoint_cache_key .= '_' . $k . '_' . $v;
        }

        $this->addCacheTag($endpoint_cache_key);
    }

    public function setFields(array $fields): self
    {
        $this->fields = array_unique($fields);
        return $this;
    }

    public function addField(string $field): self
    {
        if(! in_array($field, $this->fields)) {
            $this->fields[] = $field;
        }
        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function setQuery(array $query): self
    {
        $this->query = $query;
        $this->updateEndpointCacheKey();
        return $this;
    }

    public function addQuery(string $name, string $value): self
    {
        $this->query[$name] = $value;
        $this->updateEndpointCacheKey();
        return $this;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setUser(Authenticatable $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function setCacheTags(array $cache_tags): self
    {
        $this->cache_tags = array_unique($cache_tags);
        return $this;
    }

    public function addCacheTag(string $tag): self
    {
        if(! in_array($tag, $this->cache_tags)) {
            $this->cache_tags[] = $tag;
        }
        return $this;
    }

    public function setCacheKey(string $cache_key): self
    {
        $this->cache_key = $cache_key;
        return $this;
    }

    public function setCacheTTL(int $cache_ttl): self
    {
        $this->cache_ttl = $cache_ttl;
        return $this;
    }

    public function setCacheTagsDelete(array $cache_tags_delete): self
    {
        $this->cache_tags_delete = array_unique($cache_tags_delete);
        return $this;
    }

    public function addCacheTagDelete(string $tag): self
    {
        if(! in_array($tag, $this->cache_tags_delete)) {
            $this->cache_tags_delete[] = $tag;
        }
        return $this;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setJsonKey(string $json_key): self
    {
        $this->json_key = $json_key;
        return $this;
    }

    public function getCacheKey(): string
    {
        if(empty($this->cache_key)) {
            $this->cache_key = implode('_', $this->cache_tags);
        }
        return $this->cache_key;
    }

}