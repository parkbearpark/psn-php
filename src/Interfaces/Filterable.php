<?php
namespace Tustin\PlayStation\Interfaces;

interface Filterable
{
    /**
     * Creates a new filter for the iterator.
     *
     * @param string $filterClass
     * @param mixed ...$args
     * @return mixed
     */
    function filter(string $filterClass, ...$args);

    /**
     * Filters items based on the condition set in the callback.
     *
     * @param callable $callback
     * @return mixed
     */
    function where(callable $callback);

    /**
     * Returns the first item in the terator.
     *
     * @return mixed
     */
    function first();
}