<?php
namespace Tustin\PlayStation\Iterator;

use Tustin\PlayStation\Enum\StoryType;
use Tustin\PlayStation\Api\Model\Story;
use Tustin\PlayStation\Filter\Trophy\TrophyTypeFilter;

class CondensedStoryIterator extends AbstractInternalIterator
{
    public function __construct(array $stories = [])
    {
        $this->create(function ($story) {
            return new Story($story);
        }, $stories);
    }

    public function ofTypes(StoryType ...$types) : CondensedStoryIterator
    {
        return $this->filter(TrophyTypeFilter::class, ...$types);
    }
}