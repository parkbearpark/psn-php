<?php
namespace Tustin\PlayStation\Iterator;

use Iterator;
use Countable;
use Generator;
use ArrayIterator;
use MultipleIterator;
use IteratorAggregate;
use Tustin\PlayStation\Api\Model\Trophy;
use Tustin\PlayStation\Api\Enum\TrophyType;
use Tustin\PlayStation\Filter\Trophy\TrophyTypeFilter;
use Tustin\PlayStation\Filter\Trophy\TrophyHiddenFilter;

class TrophyIterator implements IteratorAggregate, Countable
{
    private $iterator;

    public function __construct(array $items)
    {
        $this->iterator = new ArrayIterator($items);
    }

    public function ofTypes(TrophyType ...$types)
    {
        $this->iterator = new TrophyTypeFilter($this->iterator, ...$types);

        return $this;
    }
    
    public function ofType(TrophyType $type)
    {
        return $this->ofTypes($type);
    }

    public function platinum()
    {
        return $this->ofType(TrophyType::platinum());
    }
    
    public function bronze()
    {
        return $this->ofType(TrophyType::bronze());
    }

    public function silver()
    {
        return $this->ofType(TrophyType::silver());
    }

    public function gold()
    {
        return $this->ofType(TrophyType::gold());
    }

    public function hidden()
    {
        $this->iterator = new TrophyHiddenFilter($this->iterator, true);

        return $this;
    }

    public function getIterator()
    {
        yield from $this->iterator;
    }

    public function count()
    {
        return count($this->members);
    }
}