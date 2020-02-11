<?php
namespace Tustin\PlayStation\Iterator;

use Tustin\PlayStation\Api\Model\TrophyGroup;
use Tustin\PlayStation\Api\Model\TrophyTitle;

class TrophyGroupIterator extends AbstractInternalIterator
{
    public function __construct(TrophyTitle $title, array $trophyGroups)
    {
        $this->create(function ($group) use ($title) {
            return new TrophyGroup($group, $title);
        }, $trophyGroups);
    }
}