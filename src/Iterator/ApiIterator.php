<?php
namespace Tustin\PlayStation\Iterator;


trait ApiIterator
{
    protected int $currentOffset = 0;

    protected int $currentIndexer = 0;

    protected int $limit;

    protected int $totalResults;

    protected array $cache = [];


    public function key() : int
    {
        return $this->currentOffset;
    }

    public function getTotalResults() : int
    {
        return $this->totalResults;
    }

    private function setTotalResults(int $results) : void
    {
        $this->totalResults = $results;
    }

    public function valid()
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