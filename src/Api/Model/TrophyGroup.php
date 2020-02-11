<?php
namespace Tustin\PlayStation\Api\Model;

use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Contract\Fetchable;
use Tustin\PlayStation\Iterator\TrophyIterator;

class TrophyGroup extends Api implements Fetchable
{
    use Model;

    private $trophyTitle;

    public function __construct(object $data, TrophyTitle $title)
    {
        parent::__construct($title->httpClient);

        $this->setCache($data);
        $this->trophyTitle = $title;
    }

    public function title() : TrophyTitle
    {
        return $this->trophyTitle;
    }
    
    /**
     * Gets all the trophies in the trophy group.
     *
     * @return TrophyIterator
     */
    public function trophies() : TrophyIterator
    {
        return new TrophyIterator($this->pluck('trophies', true));
    }

    /**
     * Gets the trophy group name.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->pluck('trophyGroupName');
    }

    /**
     * Gets the trophy group detail.
     *
     * @return string
     */
    public function detail() : string
    {
        return $this->pluck('trophyGroupDetail');
    }

    /**
     * Gets the trophy group ID.
     *
     * @return string
     */
    public function id() : string
    {
        return $this->pluck('trophyGroupId');
    }

    /**
     * Gets the trophy group icon URL.
     *
     * @return string
     */
    public function iconUrl() : string
    {
        return $this->pluck('trophyGroupIconUrl');
    }
    
    public function fetch() : object
    {
        return $this->get(
            'https://us-tpy.np.community.playstation.net/trophy/v1/trophyTitles/' . $this->title()->npCommunicationId() .'/trophyGroups/' . $this->id() .'/trophies',
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
                'npLanguage' => $this->title()->language()->getValue()
            ]
        );
    }
}