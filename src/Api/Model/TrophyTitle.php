<?php
namespace Tustin\PlayStation\Api\Model;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Api\TrophyGroups;
use Tustin\PlayStation\Enum\ConsoleType;
use Tustin\PlayStation\Enum\LanguageType;
use Tustin\PlayStation\Contract\Fetchable;
use Tustin\PlayStation\Iterator\TrophyGroupIterator;

class TrophyTitle extends Api
{
    use Model;

    private $language;

    public function __construct(Client $client, object $data, LanguageType $language = null)
    {
        parent::__construct($client);
        
        $this->setCache($data);

        if (is_null($language))
        {
            $language = LanguageType::english();
        }

        $this->language = $language;
    }

    /**
     * Gets all the trophy groups for the trophy title.
     *
     * @return TrophyGroups
     */
    public function trophyGroups() : TrophyGroups
    {
        return new TrophyGroups($this);
        // return new TrophyGroupIterator($this, $this->pluck('trophyGroups', true));
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
        return $this->pluck('hasTrophyGroups');
    }

    /**
     * Gets the name of the title.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->pluck('trophyTitleName');
    }

    /**
     * Gets the detail of the title.
     *
     * @return string
     */
    public function detail() : string
    {
        return $this->pluck('trophyTitleDetail');
    }

    /**
     * Gets the icon URL for the title.
     *
     * @return string
     */
    public function iconUrl() : string
    {
        return $this->pluck('trophyTitleIconUrl');
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
        // @CheckMe
        return new ConsoleType($this->pluck('trophyTitlePlatform'));
    }

    /**
     * Checks if this title has trophies.
     *
     * @return boolean
     */
    public function hasTrophies() : bool
    {
        $value = $this->pluck('definedTrophies');
        
        return isset($value) && !empty($value);
    }

    /**
     * Checks if this title has a platinum trophy.
     *
     * @return boolean
     */
    public function hasPlatinum() : bool
    {
        return $this->pluck('definedTrophies.platinum') ?? false;
    }

    /**
     * Gets the total trophy count for this title.
     *
     * @return integer
     */
    public function trophyCount() : int
    {
        $count = ($this->bronzeTrophyCount() + $this->silverTrophyCount() + $this->goldTrophyCount());
        
        if ($this->hasPlatinum())
        {
            $count++;
        }

        return $count;
    }

    /**
     * Gets the amount of bronze trophies.
     *
     * @return integer
     */
    public function bronzeTrophyCount() : int
    {
        return $this->pluck('definedTrophies.bronze');
    }

    /**
     * Gets the amount of silver trophies.
     *
     * @return integer
     */
    public function silverTrophyCount() : int
    {
        return $this->pluck('definedTrophies.silver');
    }

    /**
     * Gets the amount of gold trophies.
     *
     * @return integer
     */
    public function goldTrophyCount() : int
    {
        return $this->pluck('definedTrophies.gold');
    }

    /**
     * Gets the NP communication ID (NPWR_) for this trophy title.
     *
     * @return string
     */
    public function npCommunicationId() : string
    {
        return $this->pluck('npCommunicationId');
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
}