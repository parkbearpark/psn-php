<?php
namespace Tustin\PlayStation\Iterator;

use Countable;
use IteratorAggregate;
use Tustin\PlayStation\Enum\TrophyType;
use Tustin\PlayStation\Api\Model\Trophy;
use Tustin\PlayStation\Traits\Chainable;
use Tustin\PlayStation\Filter\Trophy\TrophyTypeFilter;
use Tustin\PlayStation\Filter\Trophy\TrophyHiddenFilter;
use Tustin\PlayStation\Filter\Trophy\TrophyRarityFilter;

class TrophyIterator implements IteratorAggregate, Countable
{
    use Chainable;

    public function __construct(array $trophies)
    {
        $this->create($trophies, Trophy::class);
    }

    /**
     * Filters trophies by multiple types of trophy.
     *
     * @param TrophyType ...$types
     * @return TrophyIterator
     */
    public function ofTypes(TrophyType ...$types) : TrophyIterator
    {
        return $this->filter(TrophyTypeFilter::class, ...$types);
    }

    /**
     * Filters trophies by whether they are hidden or not.
     *
     * @param boolean $toggle
     * @return TrophyIterator
     */
    public function hidden(bool $toggle = true) : TrophyIterator
    {
        return $this->filter(TrophyHiddenFilter::class, $toggle);
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
        return $this->filter(TrophyRarityFilter::class, $value, $lessThan);
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
}