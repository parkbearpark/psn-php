<?php
namespace Tustin\PlayStation\Traits;

use ArrayIterator;

trait Chainable
{
    private $iterator;

    /**
     * Creates the controlled iterator to allow chaining.
     *
     * @param array $items
     * @param string $class
     * @return void
     */
    protected final function create(array $items, string $class) : void
    {
        $this->iterator = new ArrayIterator(array_map(function($item) use ($class) {
            return new $class($item);
        }, $items));
    }

    /**
     * Creates a new filter for the iterator.
     *
     * @param string $filterClass
     * @param mixed ...$args
     * @return self
     */
    protected final function filter(string $filterClass, ...$args) : self
    {
        $this->iterator = new $filterClass($this->iterator, ...$args);

        return $this;
    }

    public function getIterator() : \Generator
    {
        yield from $this->iterator;
    }

    public function count() : int
    {
        return iterator_count($this->iterator);
    }
}