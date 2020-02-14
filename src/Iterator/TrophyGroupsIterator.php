<?php
namespace Tustin\PlayStation\Iterator;

use Tustin\PlayStation\Api\Model\TrophyGroup;
use Tustin\PlayStation\Api\Model\TrophyTitle;

class TrophyGroupsIterator extends AbstractApiIterator
{
    /**
     * Current trophy title.
     *
     * @var TrophyTitle
     */
    private $title;

    public function __construct(TrophyTitle $title)
    {
        parent::__construct($title->httpClient);

        $this->title = $title;

        $this->access(0);
    }

    public function access($cursor) : void
    {
        $results = $this->get(
            'https://us-tpy.np.community.playstation.net/trophy/v1/trophyTitles/' . $this->title->npCommunicationId() .'/trophyGroups',
            [
                'fields' => implode(',', [
                    '@default'
                ]),
                'npLanguage' => $this->title->language()->getValue()
            ]
        );

        $this->update(count($results->trophyGroups), $results->trophyGroups);
    }

    public function current()
    {
        return new TrophyGroup(
            $this->title,
            $this->getFromOffset($this->currentOffset),
        );
    }
}