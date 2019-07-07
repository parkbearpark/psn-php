<?php

namespace Tustin\PlayStation\Api\Trophy;

use Tustin\PlayStation\Client;

use Tustin\PlayStation\Api\User;
use \Tustin\PlayStation\Api\Game;

class TrophyGroup extends AbstractApi 
{
    private $groupData;
    private $game;

    public function __construct(Client $client, object $groupData, Game $game) 
    {
        parent::__construct($client);

        $this->groupData = $groupData;
        $this->game = $game;
    }

    /**
     * Get TrophyGroup ID.
     * 
     * Examples: default, 001, 002, etc
     *
     * @return string
     */
    public function id() : string
    {
        return $this->grogroupDataup->trophyGroupId;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->groupData->trophyGroupName;
    }

    /**
     * Get detail.
     *
     * @return string
     */
    public function detail() : string
    {
        return $this->groupData->trophyGroupDetail;
    }

    /**
     * Get the icon URL.
     *
     * @return string
     */
    public function iconUrl() : string
    {
        return $this->groupData->trophyGroupIconUrl;
    }

    /**
     * Get amount of Trophies.
     *
     * @return int
     */
    public function trophyCount() : int 
    {
        return Trophy::calculateTrophies($this->groupData->definedTrophies);
    }

    /**
     * Get completion progress of TrophyGroup.
     *
     * @return int
     */
    public function progress() : int
    {
        return $this->comparing() ?
        $this->groupData->comparedUser->progress :
        $this->groupData->fromUser->progress;
    }

    /**
     * Get Trophy earn date.
     *
     * @return \DateTime
     */
    public function lastEarnedDate() : \DateTime
    {
        return new \DateTime(
            $this->comparing() ?
            $this->groupData->comparedUser->lastUpdateDate :
            $this->groupData->fromUser->lastUpdateDate
        );
    }

    /**
     * Get last TrophyGroup earned DateTIme.
     *
     * @return \DateTime
     */
    public function lastUpdateDate() : \DateTime 
    {
        return new \DateTime($this->groupData->lastUpdateDate);
    }

    /**
     * Get all the trophies in the trophy group.
     *
     * @return array Array of \Tustin\PlayStation\Api\Trophy\Trophy
     */
    public function trophies() : array 
    {
        $returnTrophies = [];

        $data = [
            'fields' => '@default,trophyRare,trophyEarnedRate,hasTrophyGroups,trophySmallIconUrl',
            'iconSize' => 'm',
            'visibleType' => 1,
            'npLanguage' => 'en'
        ];

        if ($this->comparing()) {
            $data['comparedUser'] = $this->game()->user()->onlineId();
        }

        $trophies = $this->client->get(sprintf(Trophy::TROPHY_ENDPOINT . 'trophyTitles/%s/trophyGroups/%s/trophies', $this->game()->communicationId(), $this->id()), $data);

        foreach ($trophies->trophies as $trophy) {
            $returnTrophies[] = new Trophy($this->client, $trophy, $this);
        }

        return $returnTrophies;
    }

    /**
     * Get the game that the trophy group belongs to.
     *
     * @return \Tustin\PlayStation\Api\Game
     */
    public function game() : Game
    {
        return $this->game;
    }

    /**
     * Returns whether or not the game is for another user.
     *
     * @return bool
     */
    public function comparing() : bool
    {
        return $this->game()->isComparing();
    }
}
