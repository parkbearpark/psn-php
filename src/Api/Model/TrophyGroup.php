<?php
namespace Tustin\PlayStation\Api\Model;

use ArrayIterator;
use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Enum\LanguageType;
use Tustin\PlayStation\Iterator\TrophyIterator;

class TrophyGroup extends Model
{
    private string $npCommunicationId;
    private LanguageType $language;
    private string $groupId;

    public function __construct(TrophyTitle $title, string $groupId)
    {
        parent::__construct($title->httpClient);

        $this->npCommunicationId = $title->npCommunicationId();
        $this->language = $title->language();
        $this->groupId = $groupId;
    }

    /**
     * Gets all the trophies in the trophy group.
     *
     * @return TrophyIterator
     */
    public function trophies() : TrophyIterator
    {
        return new TrophyIterator($this->info()->trophies);
    }

    /**
     * Gets the trophy group name.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->info()->trophyGroupName;
    }

    /**
     * Gets the trophy group detail.
     *
     * @return string
     */
    public function detail() : string
    {
        return $this->info()->trophyGroupDetail;
    }

    /**
     * Gets the trophy group ID.
     *
     * @return string
     */
    public function id() : string
    {
        return $this->info()->trophyGroupId;
    }

    /**
     * Gets the trophy group icon URL.
     *
     * @return string
     */
    public function iconUrl() : string
    {
        return $this->info()->trophyGroupIconUrl;
    }
    
    /**
     * Gets the raw trophy group info from the PlayStation API.
     *
     * @return object
     */
    public function info() : object
    {
        return $this->cache ??= $this->get(
            'https://us-tpy.np.community.playstation.net/trophy/v1/trophyTitles/' . $this->npCommunicationId .'/trophyGroups/' . $this->groupId .'/trophies',
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
                'npLanguage' => $this->language->getValue()
            ]
        );
    }
}