<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use IteratorAggregate;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\TrophyGroup;
use Tustin\PlayStation\Iterator\TrophyIterator;

class Trophies extends Api implements IteratorAggregate
{
    /**
     * The trophy groups' title.
     *
     * @var TrophyGroup
     */
    private $group;
    
    private array $platforms = [];

    private string $withName = '';
    private string $withDetail = '';

    public function __construct(TrophyGroup $group)
    {
        parent::__construct($group->title()->httpClient);

        $this->group = $group;
    }

    /**
     * Gets the iterator and applies any filters.
     *
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        $iterator = new TrophyIterator($this->group);

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