<?php
namespace Diagro\API\Response;

use ArrayAccess;
use Countable;
use Diagro\API\Response;
use LogicException;

class Json extends Response implements ArrayAccess, Countable
{

    /**
     * @inheritDoc
     */
    protected function handle(string $body): mixed
    {
        return json_decode($body, true);
    }


    public function offsetExists($offset)
    {
        return ($this->hasResponse() && isset($this->processed[$offset]));
    }


    public function offsetGet($offset)
    {
        return $this->processed[$offset];
    }


    public function offsetSet($offset, $value)
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }


    public function offsetUnset($offset)
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }


    public function count(): int
    {
        return count($this->processed);
    }

}