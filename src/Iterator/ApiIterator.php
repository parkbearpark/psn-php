<?php
namespace Tustin\PlayStation\Iterator;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;

abstract class ApiIterator extends Api implements \Iterator, \Countable
{
    protected $currentOffset;

    protected int $limit;

    protected int $totalResults;

    protected array $cache = [];

    /**
     * Access a specific cursor in the API.
     * 
     * @TODO: Update this with a union type when that gets added to PHP.
     *
     * @param mixed $cursor
     * @return void
     */
    public abstract function access($cursor);

    public function key()
    {
        return $this->currentOffset;
    }

    public final function count() : int
    {
        return $this->getTotalResults();
    }

    public final function getTotalResults() : int
    {
        return $this->totalResults;
    }

    protected final function setTotalResults(int $results) : void
    {
        $this->totalResults = $results;
    }

    public final function valid()
    {
        return array_key_exists($this->currentOffset, $this->cache);
    }

    public function rewind()
    {
        $this->currentOffset = 0;
    }

    public final function update(int $totalResults, array $items)
    {
        $this->setTotalResults($totalResults);

        $this->cache = array_merge($this->cache, $items);
    }

    public function next()
    {
        $this->currentOffset++;

        if (($this->currentOffset % $this->limit) == 0)
        {
            $this->access($this->currentOffset);
        }
    }

    public function getFromOffset($offset)
    {
        if (!$this->offsetExists($offset))
        {
            throw new \InvalidArgumentException("Offset $offset does not exist.");
        }

        if (!array_key_exists($offset, $this->cache))
        {
            $this->access($offset);
        }

        return $this->cache[$offset];
    }

    public function offsetExists($offset) : bool
    {
        return $offset >= 0 && $offset < $this->getTotalResults();
    }

    protected final function appendToCache(array $items)
    {
        $this->cache = array_merge($this->cache, $items);
    }
}