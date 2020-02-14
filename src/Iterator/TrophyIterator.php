<?php
namespace Tustin\PlayStation\Iterator;

use Tustin\PlayStation\Api\Model\Trophy;
use Tustin\PlayStation\Api\Model\TrophyGroup;

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

    public function access($cursor) : void
    {
        $results = $this->get(
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

        $this->update(count($results->trophyGroups), $results->trophyGroups);
    }

    public function current()
    {
        return new Trophy(
            $this->group,
            $this->getFromOffset($this->currentOffset),
        );
    }
}