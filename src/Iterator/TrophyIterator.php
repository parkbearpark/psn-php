<?php
namespace Tustin\PlayStation\Iterator;

use Tustin\PlayStation\Enum\TrophyType;
use Tustin\PlayStation\Api\Model\Trophy;
use Tustin\PlayStation\Api\Model\TrophyGroup;
use Tustin\PlayStation\Filter\Trophy\TrophyTypeFilter;
use Tustin\PlayStation\Filter\Trophy\TrophyHiddenFilter;
use Tustin\PlayStation\Filter\Trophy\TrophyRarityFilter;

class TrophyIterator extends AbstractApiIterator
{
    /**
     * Current trophy title.
     *
     * @var TrophyGroup
     */
    private $group;
    
    public function __construct(TrophyGroup $group)
    {
        parent::__construct($group->title()->httpClient);

        $this->group = $group;

        $this->access(0);
    }

    public function fetch() : object
    {
        return $this->get(
            'https://us-tpy.np.community.playstation.net/trophy/v1/trophyTitles/' . $this->group->title()->npCommunicationId() .'/trophyGroups/' . $this->group->id() .'/trophies',
            [
                'fields' => implode(',', [
                    '@default',
                    'trophyRare',
                    'trophyEarnedRate',
                    'hasTrophyGroups',
                    'trophySmallIconUrl',
                ]),
                'iconSize' => 'm',
                'visibleType' => 1, // ???,
                'npLanguage' => $this->group->title()->language()->getValue()
            ]
        );
    }

    public function current()
    {
        return new Trophy(
            $this->group,
            $this->getFromOffset($this->currentOffset),
        );
    }
}