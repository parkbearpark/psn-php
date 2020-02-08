<?php
namespace Tustin\PlayStation\Iterator;

use Iterator;
use Countable;
use ArrayIterator;
use IteratorAggregate;
use Tustin\PlayStation\Enum\TrophyType;
use Tustin\PlayStation\Filter\Trophy\TrophyTypeFilter;
use Tustin\PlayStation\Filter\Trophy\TrophyHiddenFilter;
use Tustin\PlayStation\Filter\Trophy\TrophyRarityFilter;

class TrophyIterator implements IteratorAggregate, Countable
{
    private $iterator;

    public function __construct(array $items)
    {
        $this->iterator = new ArrayIterator($items);
    }

    /**
     * Filters trophies by multiple types of trophy.
     *
     * @param TrophyType ...$types
     * @return TrophyIterator
     */
    public function ofTypes(TrophyType ...$types) : TrophyIterator
    {
        $this->iterator = new TrophyTypeFilter($this->iterator, ...$types);

        return $this;
    }

    /**
     * Filters trophies by whether they are hidden or not.
     *
     * @param boolean $toggle
     * @return TrophyIterator
     */
    public function hidden(bool $toggle = true) : TrophyIterator
    {
        $this->iterator = new TrophyHiddenFilter($this->iterator, $toggle);

        return $this;
    }

    /**
     * Filters trophies by their earned rate.
     *
     * @param float $value
     * @param bool $lessThan
     * @return TrophyIterator
     */
    public function earnedRate(float $value, bool $lessThan = true) : TrophyIterator
    {
        $this->iterator = new TrophyRarityFilter($this->iterator, $value, $lessThan);

        return $this;
    }

    /**
     * Filters trophies by the single type of trophy.
     *
     * @param TrophyType $type
     * @return TrophyIterator
     */
    public function ofType(TrophyType $type) : TrophyIterator
    {
        return $this->ofTypes($type);
    }

    /**
     * Gets the platinum trophy.
     *
     * @return TrophyIterator
     */
    public function platinum() : TrophyIterator
    {
        return $this->ofType(TrophyType::platinum());
    }
    
    /**
     * Gets the bronze trophies.
     *
     * @return TrophyIterator
     */
    public function bronze() : TrophyIterator
    {
        return $this->ofType(TrophyType::bronze());
    }

    /**
     * Gets the silver trophies.
     *
     * @return TrophyIterator
     */
    public function silver() : TrophyIterator
    {
        return $this->ofType(TrophyType::silver());
    }

    /**
     * Gets the gold trophies.
     *
     * @return TrophyIterator
     */
    public function gold() : TrophyIterator
    {
        return $this->ofType(TrophyType::gold());
    }

    /**
     * Gets the current iterator.
     *
     * @return Iterator
     */
    public function getIterator() : Iterator
    {
        yield from $this->iterator;
    }

    /**
     * Gets the amount of items in the iterator.
     *
     * @return int
     */
    public function count() : int
    {
        return iterator_count($this->iterator);
    }
}