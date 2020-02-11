<?php
namespace Tustin\PlayStation\Iterator;

use Countable;
use Generator;
use ArrayIterator;
use IteratorAggregate;
use CallbackFilterIterator;
use Tustin\PlayStation\Contract\Filterable;

abstract class AbstractInternalIterator implements IteratorAggregate, Countable, Filterable
{
    private $iterator;

    /**
     * Creates a new instance of an iterator for the internal iterator.
     *
     * @param array $items
     * @param string $class
     * @param mixed ...$args
     * @return void
     */
    protected final function create(callable $callback, array $items) : void
    {
        $this->iterator = new ArrayIterator(
            array_map($callback, $items)
        );
    }

    public function getIterator() : Generator
    {
        yield from $this->iterator;
    }

    public function count() : int
    {
        return iterator_count($this->iterator);
    }

    public function where(callable $callback) : self
    {
        $this->iterator = new CallbackFilterIterator($this->iterator, $callback);

        return $this;
    }

    public function filter(string $filterClass, ...$args) : self
    {
        $this->iterator = new $filterClass($this->iterator, ...$args);

        return $this;
    }
}