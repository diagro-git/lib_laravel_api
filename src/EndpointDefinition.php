<?php
namespace Diagro\API;

use Illuminate\Contracts\Auth\Authenticatable;

class EndpointDefinition
{

    public array $fields = [];

    public array $data = [];

    public array $query = [];

    public array $headers = [];

    public ?Authenticatable $user = null;

    public array $cache_tags = [];

    public ?string $cache_key = null;

    public int $cache_ttl = 3600;

    public array $cache_tags_delete = [];

    public int $timeout = 30;

    public string $json_key = 'data';


    public function __construct(
        public string $identifier,
        public string $url,
        public RequestMethod $method,
        public string $bearer_token,
        public int $app_id
    )
    {}

    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function addField(string $field): self
    {
        $this->fields[] = $field;
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
        return $this;
    }

    public function addQuery(string $name, string $value): self
    {
        $this->query[$name] = $value;
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
        $this->cache_tags = $cache_tags;
        return $this;
    }

    public function addCacheTag(string $tag): self
    {
        $this->cache_tags[] = $tag;
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
        $this->cache_tags_delete = $cache_tags_delete;
        return $this;
    }

    public function addCacheTagDelete(string $tag): self
    {
        $this->cache_tags_delete[] = $tag;
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