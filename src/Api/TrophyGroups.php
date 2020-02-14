<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use IteratorAggregate;
use Tustin\PlayStation\Api\Model\TrophyGroup;
use Tustin\PlayStation\Api\Model\TrophyTitle;
use Tustin\PlayStation\Iterator\TrophyGroupsIterator;
use Tustin\PlayStation\Iterator\Filter\TrophyGroup\TrophyGroupNameFilter;
use Tustin\PlayStation\Iterator\Filter\TrophyGroup\TrophyGroupDetailFilter;

class TrophyGroups implements IteratorAggregate
{
    /**
     * The trophy groups' title.
     *
     * @var TrophyTitle
     */
    private $title;

    private string $withName = '';
    private string $withDetail = '';

    public function __construct(TrophyTitle $title)
    {
        $this->title = $title;
    }

    public function withName(string $name)
    {
        $this->withName = $name;

        return $this;
    }

    public function withDetail(string $detail)
    {
        $this->withDetail = $detail;
        
        return $this;
    }

    public function withCertainTrophyCount(string $trophyName, int $count)
    {
        // 
    }

    public function withTotalTrophyCount(int $count)
    {
        // 
    }

    /**
     * Gets the iterator and applies any filters.
     *
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        $iterator = new TrophyGroupsIterator($this->title);

        if ($this->withName)
        {
            $iterator = new TrophyGroupNameFilter($iterator, $this->withName);
        }

        if ($this->withDetail)
        {
            $iterator = new TrophyGroupDetailFilter($iterator, $this->withDetail);
        }

        return $iterator;
    }

    /**
     * Gets the first trophy title in the collection.
     *
     * @return TrophyGroup
     */
    public function first() : TrophyGroup
    {
        return $this->getIterator()->current();
    }
}