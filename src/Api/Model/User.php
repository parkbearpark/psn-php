<?php

namespace Tustin\PlayStation\Api\Model;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Model\Model;
use Tustin\PlayStation\Iterator\FriendsIterator;

class User extends Model
{
    private string $onlineIdParameter;

    public function __construct(Client $client, string $onlineId)
    {
        parent::__construct($client);

        $this->onlineIdParameter = $onlineId;
    }

    public function friends(string $sort = 'onlineStatus', int $limit = 36)
    {
        return new FriendsIterator($this->httpClient, $this->onlineIdParameter, $sort, $limit);
    }

    public function aboutMe() : string
    {
        return $this->profile()->aboutMe;
    }

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

    public function isBlocking() : bool
    {
        return $this->profile()->blocking;
    }

    public function followerCount() : int
    {
        return $this->profile()->followerCount;
    }

    public function isFollowing() : bool
    {
        return $this->profile()->following;
    }

    public function isVerified() : bool
    {
        return $this->profile()->isOfficiallyVerified;
    }

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

    public function hasMutualFriends() : bool
    {
        return $this->mutualFriendCount() > 0;
    }

    public function isCloseFriend() : bool
    {
        return ($this->info()->personalDetailSharing !== 'no');
    }

    public function hasFriendRequested() : bool
    {
        return ($this->info()->friendRelation == 'requesting');
    }

    public function isOnline() : bool
    {
        return $this->info()->presences[0]->onlineStatus == "online";
    }

    public function onlineId() : string
    {
        return $this->profile()->onlineId;
    }

    public function hasPlus() : bool
    {
        return $this->profile()->plus;
    }

    public function profile() : object
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