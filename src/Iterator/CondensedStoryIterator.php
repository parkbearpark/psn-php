<?php
namespace Tustin\PlayStation\Iterator;

use CallbackFilterIterator;
use Tustin\PlayStation\Enum\StoryType;
use Tustin\PlayStation\Api\Model\Story;
use Tustin\PlayStation\Traits\Filterable;
use Tustin\PlayStation\Filter\Trophy\TrophyTypeFilter;

class CondensedStoryIterator extends AbstractInternalIterator
{
    use Filterable;

    public function __construct(array $stories = [])
    {
        $this->create($stories, Story::class);
    }

    public function ofTypes(StoryType ...$types) : CondensedStoryIterator
    {
        $this->iterator = $this->filter(TrophyTypeFilter::class, ...$types);

        return $this;
    }
}