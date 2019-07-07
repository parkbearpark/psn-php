<?php

namespace Tustin\PlayStation\Api;

use Tustin\PlayStation\Client;

use Tustin\PlayStation\Api\User;
use Tustin\PlayStation\Api\Trophy;

class Game extends AbstractApi 
{
    const GAME_ENDPOINT = 'https://gamelist.api.playstation.com/v1/';

    private $titleId;
    private $npCommunicationId;
    private $game;
    private $user;

    /**
     * New instance of \Tustin\PlayStation\Api\Game.
     *
     * @param \Tustin\PlayStation\Client $client The client
     * @param string $titleId The game's title ID
     * @param \Tustin\PlayStation\Api\User $user
     */
    public function __construct(Client $client, string $titleId, User $user = null)
    {
        parent::__construct($client);

        $this->titleId = $titleId;
        $this->user = $user;
    }

    /**
     * Gets the title ID for the game.
     *
     * @return string
     */
    public function titleId() : string
    {
        return $this->titleId;
    }

    /**
     * Gets the name of the game's trophy set.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->trophyInfo()->trophyTitleName ?? '';
    }
    
    /**
     * Gets the game's image URL.
     *
     * @return string
     */
    public function imageUrl() : string 
    {
        return $this->trophyInfo()->trophyTitleIconUrl ?? '';
    }

    /**
     * Gets the game's NP communication ID.
     *
     * @return string
     */
    public function communicationId() : string
    {
        return $this->trophyInfo()->npCommunicationId ?? '';
    }

    /**
     * Checks if the game has trophies.
     *
     * @return bool
     */
    public function hasTrophies() : bool
    {
        return ($this->trophyInfo() !== null);
    }

    /**
     * Checks if the User has earned the platinum trophy.
     *
     * @return bool
     */
    public function earnedPlatinum() : bool
    {
        if (
            $this->trophyInfo() === null || 
            !isset($this->trophyInfo()->definedTrophies->platinum) || 
            !$this->trophyInfo()->definedTrophies->platinum
        ) {
            return false;
        }

        $user = $this->userTrophyInfo();

        return ($user === false) ? false : boolval($user->earnedTrophies->platinum);
    }

    /**
     * Gets the trophy information for the Game.
     *
     * @return object|null
     */
    public function trophyInfo() : ?object
    {
        if ($this->game === null) {
            // Kind of a hack here.
            // This endpoint doesn't give exactly the same information as the proper game endpoint would,
            // But I wasn't able to find a way to get info from the gane endpoint with just a titleId.
            // It works, but I'd rather it be more consistent with the other endpoint.
            $game = $this->client->get(Trophy::TROPHY_ENDPOINT . 'apps/trophyTitles', [
                'npTitleIds' => $this->titleId,
                'fields' => '@default',
                'npLanguage' => 'en'
            ]);
            
            if (!count($game->apps) || !count($game->apps[0]->trophyTitles)) return null;

            $this->npCommunicationId = $game->apps[0]->trophyTitles[0]->npCommunicationId;

            $data = [
                'npLanguage' => 'en'
            ];

            if ($this->isComparing()) {
                $data['comparedUser'] = $this->user()->onlineId();
            }

            $game = $this->client->get(sprintf(Trophy::TROPHY_ENDPOINT . 'trophyTitles/%s', $this->npCommunicationId), $data);

            if ($game->totalResults !== 1 || !count($game->trophyTitles)) return null;

            $this->game = $game->trophyTitles[0];
        }

        return $this->game;
    }


    /**
     * Gets the users who have played this game.
     * 
     * This will only return players who the logged in user is friends with.
     *
     * @return array Array of \Tustin\PlayStation\Api\User.
     */
    public function players() : array 
    {
        $returnPlayers = [];

        $players = $this->client->get(sprintf(self::GAME_ENDPOINT . 'titles/%s/players', $this->titleId));

        if ($players->size === 0) return $returnPlayers;

        foreach ($players->data as $player) {
            $returnPlayers[] = new User($this->client, $player->onlineId);
        }

        return $returnPlayers;
    }

    /**
     * Gets all the trophy groups for this game.
     * 
     * Each game with trophies will have at least one trophy group, that being the base trophies.
     * 
     * Subsequent trophy groups will be additional trophies added with DLC or a game update.
     *
     * @return array Array of \Tustin\PlayStation\Api\TrophyGroup
     */
    public function trophyGroups(string $iconSize = 'm', string $language = 'en') : array
    {
        $returnGroups = [];

        $data = [
            'fields' => '@default,trophyTitleSmallIconUrl,trophyGroupSmallIconUrl',
            'iconSize' => $iconSize,
            'npLanguage' => $language
        ];

        // If we're currently checking another user's trophies, we want to persist this to the trophy groups as well.
        if ($this->isComparing()) {
            $data['comparedUser'] = $this->user()->onlineId();
        }

        $groups = $this->client->get(sprintf(Trophy::TROPHY_ENDPOINT . 'trophyTitles/%s/trophyGroups', $this->communicationId()), $data);

        foreach ($groups->trophyGroups as $group) {
            $returnGroups[] = new TrophyGroup($this->client, $group, $this);
        }

        return $returnGroups;
    }
    

    /**
     * Gets all trophies for this game.
     *
     * @return array Array of \Tustin\PlayStation\Api\Trophy
     */
    public function trophies(string $iconSize = 'm', string $language = 'en') : array 
    {
        $returnTrophies = [];

        $data = [
            'fields' => '@default,trophyRare,trophyEarnedRate,hasTrophyGroups,trophySmallIconUrl',
            'iconSize' => $iconSize,
            'visibleType' => 1, // What is this for?? Maybe this only shows non-hidden trophies? - Tustin 6 July 2019
            'npLanguage' => $language
        ];

        if ($this->isComparing()) {
            $data['comparedUser'] = $this->user()->onlineId();
        }

        $trophies = $this->client->get(sprintf(Trophy::TROPHY_ENDPOINT . 'trophyTitles/%s/trophyGroups/all/trophies', $this->communicationId()), $data);

        foreach ($trophies->trophies as $trophy) {
            $returnTrophies[] = new Trophy($this->client, $trophy, $this);
        }

        return $returnTrophies;
    }


    /**
     * Gets the user who played this game.
     *
     * @return User
     */
    public function user() : ?User
    {
        return $this->user;
    }

    /**
     * Gets whether we're getting trophies for the logged in user or another user.
     *
     * @return bool
     */
    public function isComparing() : bool
    {
        if ($this->user() === null) {
            return false;
        }

        return ($this->user()->onlineId() !== null);
    }

    /**
     * Gets whether the user has played the game or not.
     *
     * @return bool
     */
    public function hasPlayed() : bool
    {
        return boolval($this->userTrophyInfo());
    }

    /**
     * Gets the trophy information from the user that is being compared to.
     *
     * @return object|null
     */
    private function comparedUserTrophyInfo() : ?object
    {
        return isset($this->trophyInfo()->comparedUser) ? $this->trophyInfo()->comparedUser : null;
    }

    /**
     * Gets the trophy information from the user that is logged in to the api.
     *
     * @return object|null
     */
    private function fromUserTrophyInfo() : ?object
    {
        return isset($this->trophyInfo()->fromUser) ? $this->trophyInfo()->fromUser : null;
    }

    /**
     * Gets the trophy information for the active user. When a compared user is set, the trophy information for the
     * compared user will be returned, otherwise the trophy information for the from user (user that is logged in to
     * the API) will be returned.
     *
     * @return object|null
     */
    public function userTrophyInfo() : ?object
    {
        // @Cleanup: Does this really need to be so convoluted?
        // - Tustin July 6 2019
        $comparedUserTrophyInfo = $this->comparedUserTrophyInfo();
        $fromUserTrophyInfo = $this->fromUserTrophyInfo();

        if ($this->isComparing() && $comparedUserTrophyInfo) {
            return $comparedUserTrophyInfo;
        } else if ($fromUserTrophyInfo) {
            return $fromUserTrophyInfo;
        }

        return null;
    }
}