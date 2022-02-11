<?php
namespace Diagro\API;

/**
 * The response class handles the response from an API request to the Diagro backend.
 *
 * @package Diagro\API
 */
abstract class Response
{

    protected mixed $processed;


    public function __construct(protected \Illuminate\Http\Client\Response $response)
    {
        $this->processed = $this->handle($this->response->body());
    }

    public function succesful(): bool
    {
        return $this->response->successful();
    }

    public function failed(): bool
    {
        return $this->response->failed();
    }

    public function status(): int
    {
        return $this->response->status();
    }

    public function error(): string
    {
        if($this->response->header("Content-Type") == "application/json") {
            return $this->response->json()['message'];
        } else {
            return $this->response->body();
        }
    }


    public function hasResponse(): bool
    {
        return ($this->processed != null);
    }

    /**
     * Handles the response.
     * The return value is stored in the processed property of this response class.
     *
     * @param string $body
     * @return mixed
     */
    abstract protected function handle(string $body): mixed;


}