<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use IteratorAggregate;
use Tustin\PlayStation\Enum\TrophyType;
use Tustin\PlayStation\Api\Model\TrophyGroup;
use Tustin\PlayStation\Api\Model\TrophyTitle;
use Tustin\PlayStation\Iterator\TrophyGroupsIterator;
use Tustin\PlayStation\Iterator\Filter\TrophyGroup\NameFilter;
use Tustin\PlayStation\Iterator\Filter\TrophyGroup\DetailFilter;
use Tustin\PlayStation\Iterator\Filter\TrophyGroup\TrophyTypeFilter;

class TrophyGroupsRepository implements IteratorAggregate
{
    /**
     * The trophy groups' title.
     *
     * @var TrophyTitle
     */
    private $title;

    private string $withName = '';
    private string $withDetail = '';

    private array $certainTrophyTypeFilter = [];

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

    public function withTrophyCount(TrophyType $trophy, string $operand, int $count)
    {
        $this->certainTrophyTypeFilter[] = [$trophy, $operand, $count];

        return $this;
    }

    public function withTotalTrophyCount(string $operand, int $count)
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
            $iterator = new NameFilter($iterator, $this->withName);
        }

        if ($this->withDetail)
        {
            $iterator = new DetailFilter($iterator, $this->withDetail);
        }

        if ($this->certainTrophyTypeFilter)
        {
            foreach ($this->certainTrophyTypeFilter as $filter)
            {
                $iterator = new TrophyTypeFilter($iterator, ...$filter);
            }
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