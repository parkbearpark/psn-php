<?php
namespace Tustin\PlayStation\Iterator;

use Tustin\PlayStation\Traits\Filterable;
use Tustin\PlayStation\Api\Model\TrophyGroup;
use Tustin\PlayStation\Api\Model\TrophyTitle;

class TrophyGroupIterator extends AbstractInternalIterator
{
    use Filterable;

    public function __construct(TrophyTitle $title, array $trophyGroups)
    {
        $this->create($trophyGroups, TrophyGroup::class, $title);
    }
}