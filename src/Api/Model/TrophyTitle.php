<?php
namespace Tustin\PlayStation\Api\Model;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Enum\ConsoleType;
use Tustin\PlayStation\Api\Enum\LanguageType;
use Tustin\PlayStation\Iterator\TrophyGroupsIterator;

class TrophyTitle extends Model
{
    private string $npCommunicationId;
    private LanguageType $language;

    public function __construct(Client $client, string $npCommunicationId, LanguageType $language)
    {
        parent::__construct($client);

        $this->npCommunicationId = $npCommunicationId;
        $this->language = $language;
    }

    /**
     * Gets all the trophy groups for the trophy title.
     *
     * @return \Generator
     */
    public function trophyGroups() : \Generator
    {
        foreach ($this->info()->trophyGroups as $group)
        {
            yield new TrophyGroup($this, $group->trophyGroupId);
        }
    }

    /**
     * Checks if this title has trophy groups.
     * 
     * These groups are typically DLC trophies.
     *
     * @return boolean
     */
    public function hasTrophyGroups() : bool
    {
        return $this->info()->hasTrophyGroups;
    }

    /**
     * Gets the name of the title.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->info()->trophyTitleName;
    }

    /**
     * Gets the detail of the title.
     *
     * @return string
     */
    public function detail() : string
    {
        return $this->info()->trophyTitleDetail;
    }

    /**
     * Gets the icon URL for the title.
     *
     * @return string
     */
    public function iconUrl() : string
    {
        return $this->info()->trophyTitleIconUrl;
    }

    /**
     * Gets the platform this title is for.
     * 
     * @TODO: This might need to return an array??
     *
     * @return ConsoleType
     */
    public function platform() : ConsoleType
    {
        return new ConsoleType($this->info()->trophyTitleIconUrl);
    }

    /**
     * Checks if this title has trophies.
     *
     * @return boolean
     */
    public function hasTrophies() : bool
    {
        return isset($this->definedTrophies) && !empty($this->definedTrophies);
    }

    /**
     * Checks if this title has a platinum trophy.
     *
     * @return boolean
     */
    public function hasPlatinum() : bool
    {
        return $this->definedTrophies->platinum ?? false;
    }

    /**
     * Gets the amount of bronze trophies.
     *
     * @return integer
     */
    public function bronzeTrophyCount() : int
    {
        return $this->definedTrophies->bronze;
    }

    /**
     * Gets the amount of silver trophies.
     *
     * @return integer
     */
    public function silverTrophyCount() : int
    {
        return $this->definedTrophies->silver;
    }

    /**
     * Gets the amount of gold trophies.
     *
     * @return integer
     */
    public function goldTrophyCount() : int
    {
        return $this->definedTrophies->gold;
    }

    /**
     * Gets the NP communication ID (NPWR_) for this trophy title.
     *
     * @return string
     */
    public function npCommunicationId() : string
    {
        return $this->npCommunicationId;
    }

    /**
     * Gets the language used for the trophy title.
     *
     * @return LanguageType
     */
    public function language() : LanguageType
    {
        return $this->language;
    }

    /**
     * Gets the raw trophy title info from the PlayStation API.
     * 
     * @return object
     */
    public function info() : object
    {
        return $this->cache ??= $this->get(
            'https://us-tpy.np.community.playstation.net/trophy/v1/trophyTitles/' . $this->npCommunicationId() .'/trophyGroups',
            [
                'fields' => implode(',', [
                    '@default'
                ]),
                'npLanguage' => $this->language->getValue()
            ]
        );
    }
}