<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use GuzzleHttp\Client;
use IteratorAggregate;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Enum\ConsoleType;
use Tustin\PlayStation\Enum\LanguageType;
use Tustin\PlayStation\Api\Model\TrophyGroup;
use Tustin\PlayStation\Api\Model\TrophyTitle;
use Tustin\PlayStation\Iterator\TrophyIterator;
use Tustin\PlayStation\Iterator\TrophyGroupIterator;
use Tustin\PlayStation\Iterator\TrophyGroupsIterator;
use Tustin\PlayStation\Iterator\TrophyTitlesIterator;
use Tustin\PlayStation\Iterator\Filter\TrophyTitle\TrophyTitleNameFilter;
use Tustin\PlayStation\Iterator\Filter\TrophyTitle\TrophyTitleHasGroupsFilter;

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