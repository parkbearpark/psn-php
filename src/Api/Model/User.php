<?php

namespace Tustin\PlayStation\Api\Model;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Model\Model;
use Tustin\PlayStation\Api\MessageThreads;
use Tustin\PlayStation\Iterator\FeedIterator;
use Tustin\PlayStation\Iterator\FriendsIterator;
use Tustin\PlayStation\Api\Message\AbstractMessage;

class User extends Model
{
    private string $onlineIdParameter;
    private bool $exact;

    public function __construct(Client $client, string $onlineId, bool $exact = false)
    {
        parent::__construct($client);

        $this->onlineIdParameter = $onlineId;
        $this->exact = $exact;
    }

    /**
     * Gets all the user's friends.
     *
     * @param string $sort
     * @param integer $limit
     * @return FriendsIterator
     */
    public function friends(string $sort = 'onlineStatus', int $limit = 36) : FriendsIterator
    {
        return new FriendsIterator($this->httpClient, $this->onlineIdParameter, $sort, $limit);
    }

    /**
     * Sends a message to the user.
     *
     * @param AbstractMessage $message
     * @return Message
     */
    public function sendMessage(AbstractMessage $message) : Message
    {
        return (new MessageThreads($this->httpClient))
        ->withOnly($this->onlineId())
        ->sendMessage($message);
    }

    /**
     * Gets all message threads containing the user.
     *
     * @return \Generator
     */
    public function messageThreads() : \Generator
    {
        yield from (new MessageThreads($this->httpClient))
        ->with($this->onlineId());
    }

    /**
     * Gets all the activity feed items for the user.
     *
     * @param boolean $includeComments
     * @param integer $limit
     * @return FeedIterator
     */
    public function feed(bool $includeComments = true, int $limit = 10) : FeedIterator
    {
        return new FeedIterator($this->httpClient, $this->onlineId(), $includeComments, $limit);
    }

    /**
     * Gets the user's about me.
     *
     * @return string
     */
    public function aboutMe() : string
    {
        return $this->profile()->aboutMe;
    }

    /**
     * Gets the user's account ID.
     *
     * @return string
     */
    public function accountId() : string
    {
        return $this->profile()->accountId;
    }

    /**
     * Returns all the available avatar URL sizes.
     * 
     * Each array key is the size of the image.
     *
     * @return array
     */
    public function avatarUrls() : array
    {
        $urls = [];

        foreach ($this->profile()->avatarUrls as $url)
        {
            $urls[$url->size] = $url->avatarUrl;
        }

        return $urls;
    }

    /**
     * Gets the avatar URL.
     * 
     * This should return the largest size available.
     *
     * @return string
     */
    public function avatarUrl() : string
    {
        $firstKey = array_key_first($this->avatarUrls());
        return $this->avatarUrls()[$firstKey];
    }

    /**
     * Check if client is blocking the user.
     *
     * @return boolean
     */
    public function isBlocking() : bool
    {
        return $this->profile()->blocking;
    }

    /**
     * Get the user's follower count.
     *
     * @return integer
     */
    public function followerCount() : int
    {
        return $this->profile()->followerCount;
    }

    /**
     * Check if the client is following the user.
     *
     * @return boolean
     */
    public function isFollowing() : bool
    {
        return $this->profile()->following;
    }

    /**
     * Check if the user is verified.
     *
     * @return boolean
     */
    public function isVerified() : bool
    {
        return $this->profile()->isOfficiallyVerified;
    }

    /**
     * Gets all the user's languages.
     *
     * @return array
     */
    public function languages() : array
    {
        return $this->profile()->languagesUsed;
    }

    /**
     * Gets mutual friend count.
     * 
     * Returns -1 if current profile is the logged in user.
     *
     * @return integer
     */
    public function mutualFriendCount() : int
    {
        return $this->profile()->mutualFriendsCount;
    }

    /**
     * Checks if the client has any mutual friends with the user. 
     *
     * @return boolean
     */
    public function hasMutualFriends() : bool
    {
        return $this->mutualFriendCount() > 0;
    }

    /**
     * Checks if the client is close friends with the user.
     *
     * @return boolean
     */
    public function isCloseFriend() : bool
    {
        return ($this->profile()->personalDetailSharing !== 'no');
    }

    /**
     * Checks if the client has a pending friend request with the user.
     * 
     * @TODO: Check if this works both ways.
     *
     * @return boolean
     */
    public function hasFriendRequested() : bool
    {
        return ($this->profile()->friendRelation == 'requesting');
    }

    /**
     * Checks if the user is currently online.
     *
     * @return boolean
     */
    public function isOnline() : bool
    {
        return $this->profile()->presences[0]->onlineStatus == "online";
    }

    /**
     * Gets the user's current online ID.
     *
     * @return string
     */
    public function onlineId() : string
    {
        return $this->exact ? $this->onlineIdParameter : $this->profile()->onlineId;
    }

    /**
     * Checks if the user has PlayStation Plus.
     *
     * @return boolean
     */
    public function hasPlus() : bool
    {
        return $this->profile()->plus;
    }

    /**
     * Gets the user's profile info from the PlayStation API.
     * 
     * Will return from cache first if info has been requested in this instance.
     *
     * @return ?object
     */
    public function profile() : ?object
    {
        return $this->cache ??= $this->get('https://us-prof.np.community.playstation.net/userProfile/v1/users/' . $this->onlineIdParameter . '/profile2', [
            'fields' => implode(',', [
                'aboutMe',
                'accountId',
                'avatarUrls',
                'blocking',
                'followerCount',
                'following',
                'friendRelation',
                'isOfficiallyVerified',
                'languagesUsed',
                'mutualFriendsCount',
                'onlineId',
                'personalDetail(@default,profilePictureUrls)',
                'personalDetailSharing',
                'primaryOnlineStatus',
                'plus',
                'presences(@titleInfo)',
                'requestMessageFlag',
                'trophySummary(@default,progress,earnedTrophies)'
            ]),
        ])->profile;
    }
}