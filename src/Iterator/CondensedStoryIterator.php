<?php
namespace Tustin\PlayStation\Iterator;

use Countable;
use IteratorAggregate;
use CallbackFilterIterator;
use Tustin\PlayStation\Enum\StoryType;
use Tustin\PlayStation\Api\Model\Story;
use Tustin\PlayStation\Traits\Chainable;
use Tustin\PlayStation\Filter\Trophy\TrophyTypeFilter;

class CondensedStoryIterator implements IteratorAggregate, Countable
{
    use Chainable;

    public function __construct(array $stories = [])
    {
        $this->create($stories, Story::class);
    }

    public function ofTypes(StoryType ...$types) : CondensedStoryIterator
    {
        return $this->filter(TrophyTypeFilter::class, ...$types);
    }

    public function where(callable $callback)
    {
        return $this->filter(CallbackFilterIterator::class, $callback);
    }
}