<?php
namespace Tustin\PlayStation\Iterator;

use Tustin\PlayStation\Api\Api;

abstract class ApiIterator extends Api implements \Iterator
{
    protected int $currentOffset = 0;

    protected int $currentIndexer = 0;

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
        return $this->currentIndexer;
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
        return array_key_exists($this->currentIndexer, $this->cache);
    }

    public function rewind()
    {
        $this->currentOffset = 0;
        $this->currentIndexer = 0;
    }

    public function next()
    {
        $this->currentOffset++;
        $this->currentIndexer++;
        if (($this->currentOffset % $this->limit) == 0)
        {
            $this->access($this->currentOffset);
            $this->currentIndexer = 0;
        }
    }
}