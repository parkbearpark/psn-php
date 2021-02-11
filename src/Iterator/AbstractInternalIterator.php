<?php
namespace Tustin\PlayStation\Iterator;

use Countable;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use CallbackFilterIterator;
use Tustin\PlayStation\Interfaces\Filterable;

abstract class AbstractInternalIterator implements IteratorAggregate, Countable, Filterable
{
    private $iterator;

    /**
     * Creates a new instance of an iterator.
     *
     * @param callable $callback
     * @param array $items
     * @return void
     */
    protected final function create(callable $callback, array $items) : void
    {
        $this->iterator = new ArrayIterator(
            array_map($callback, $items)
        );
    }

    public function getIterator() : Traversable
    {
       return $this->iterator;
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

    public function first()
    {
        $this->iterator->rewind();

        return $this->iterator->current();
    }
}