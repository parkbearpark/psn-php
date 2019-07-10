<?php

namespace Tustin\PlayStation\Api\Trophy;

use Tustin\PlayStation\Client;

use Tustin\PlayStation\Api\AbstractApi;
use Tustin\PlayStation\Api\User;

class Trophy extends AbstractApi 
{
    public const TROPHY_ENDPOINT    = 'https://us-tpy.np.community.playstation.net/trophy/v1/';

    private $trophy;
    private $trophyGroup;

    public function __construct(Client $client, object $trophy, TrophyGroup $trophyGroup) 
    {
        parent::__construct($client);
        
        $this->trophy = $trophy;
        $this->trophyGroup = $trophyGroup;        
    }

    /**
     * Gets the trophy ID.
     *
     * @return int
     */
    public function id() : int 
    {
        return $this->trophy->trophyId;
    }

    /**
     * Checks if trophy is hidden.
     *
     * @return bool
     */
    public function hidden() : bool 
    {
        return $this->trophy->trophyHidden;
    }

    /**
     * Gets the type of trophy (bronze, silver, gold, platinum).
     *
     * @return string
     */
    public function type() : string
    {
        return $this->trophy->trophyType;
    }

    /**
     * Gets the name of the trophy.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->trophy->trophyName;
    }

    /**
     * Gets the trophy's detail.
     *
     * @return string
     */
    public function detail() : string
    {
        return $this->trophy->trophyDetail;
    }

    /**
     * Gets the icon URL for the trophy.
     *
     * @return string
     */
    public function iconUrl() : string
    {
        return $this->trophy->trophyIconUrl;
    }

    /**
     * Gets the total earned rate of the trophy.
     *
     * @return float
     */
    public function earnedRate() : float
    {
        return floatval($this->trophy->trophyEarnedRate);
    }

    /**
     * Checks if User has earned the Trophy.
     *
     * @return bool
     */
    public function earned() : bool
    {
        // TODO: Temp fix for #93, for some reason the comparedUser isn't always returned from the PlayStation API.
        // Needs additional investigation but this will at least stop it bugging out.
        if ($this->comparing()) {
            if (property_exists($this->trophy, 'comparedUser')) {
                return $this->trophy->comparedUser->earned;
            }

            return false;
        }

        if (property_exists($this->trophy, 'fromUser')) {
            return $this->trophy->fromUser->earned;
        }

        return false;
    }

    /**
     * Gets the date and time when the user earned the trophy.
     *
     * @return \DateTime|null
     */
    public function earnedDate() : ?\DateTime
    {
        if (!$this->earned()) return null;

        return new \DateTime(
            $this->comparing() ?
            $this->trophy->comparedUser->earnedDate :
            $this->trophy->fromUser->earnedDate
        );
    }

    /**
     * Gets the trophy group the trophy is in.
     *
     * @return \Tustin\PlayStation\Api\TrophyGroup
     */
    public function trophyGroup() : TrophyGroup
    {
        return $this->trophyGroup;
    }

    /**
     * Gets the game the trophy is for.
     *
     * @return \Tustin\PlayStation\Api\Game
     */
    public function game() : Game
    {
        return $this->trophyGroup()->game();
    }

    /**
     * Returns whether or not the TrophySet is for another user.
     * 
     * TODO (Tustin) - This needs to be cleaned up. No wonder why this can be finicky.
     *
     * @return bool
     */
    public function comparing() : bool
    {
        return ($this->game()->user()->onlineId() !== null);
    }

    /**	
     * Calculate all the types of trophies.	
     *	
     * @param object $trophyTypes Trophy type information.	
     * @return int	
     */	
    public static function calculateTrophies(object $trophyTypes) : int
    {	
        return ($trophyTypes->bronze + $trophyTypes->silver + $trophyTypes->gold + $trophyTypes->platinum);	
    }
}