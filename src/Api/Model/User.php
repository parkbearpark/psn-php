<?php

namespace Tustin\PlayStation\Api\Model;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Api\FeedRepository;
use Tustin\PlayStation\Api\UsersRepository;
use Tustin\PlayStation\Interfaces\Fetchable;
use Tustin\PlayStation\Api\FriendsRepository;
use Tustin\PlayStation\Api\CommunitiesRepository;
use Tustin\PlayStation\Api\TrophyTitlesRepository;
use Tustin\PlayStation\Api\Message\AbstractMessage;
use Tustin\PlayStation\Api\MessageThreadsRepository;
use Tustin\PlayStation\Interfaces\RepositoryInterface;

class User extends Api implements RepositoryInterface, Fetchable
{
    use Model;
    
    private string $onlineIdParameter;
    private bool $exact;

    public function __construct(UsersRepository $usersRepository, string $onlineId, bool $exact = false)
    {
        parent::__construct($usersRepository->httpClient);

        $this->onlineIdParameter = $onlineId;
        $this->exact = $exact;
    }

    public static function create(Client $client, string $onlineId, bool $exact = false)
    {
        return new static(new UsersRepository($client), $onlineId, $exact);
    }

    /**
     * Create a new instance of User using existing API data.
     * 
     * @param UsersRepository $usersRepository
     * @param object $data
     * @return User
     */
    public static function fromObject(UsersRepository $usersRepository, object $data) : User
    {
        $instance = new static($usersRepository, $data->profile->onlineId ?? $data->onlineId, true);
        $instance->setCache($data);
        return $instance;
    }

    /**
     * Gets all the user's friends.
     *
     * @return FriendsRepository
     */
    public function friends() : FriendsRepository
    {
        return new FriendsRepository($this);
    }

    /**
     * Gets the user's trophy titles.
     *
     * @return TrophyTitlesRepository
     */
    public function trophyTitles() : TrophyTitlesRepository
    {
        return (new TrophyTitlesRepository($this->getHttpClient()))
        ->forUser($this);
    }

    /**
     * Gets the communities the user is in.
     *
     * @return CommunitiesRepository
     */
    public function communities() : CommunitiesRepository
    {
        return (new CommunitiesRepository($this->getHttpClient()))
        ->forUser($this);
    }

    /**
     * Sends a message to the user.
     *
     * @param AbstractMessage $message
     * @return Message
     */
    public function sendMessage(AbstractMessage $message) : Message
    {
        return (new MessageThreadsRepository($this->getHttpClient()))
        ->with($this->onlineId())
        ->only()
        ->first()
        ->sendMessage($message);
    }

    /**
     * Gets all message threads containing the user.
     *
     * @return MessageThreadsRepository
     */
    public function messageThreads() : MessageThreadsRepository
    {
        return (new MessageThreadsRepository($this->getHttpClient()))
        ->with($this->onlineId());
    }

    /**
     * Gets the activity feed for the current user.
     *
     * @return FeedRepository
     */
    public function feed() : FeedRepository
    {
        return (new FeedRepository($this->getHttpClient()))
        ->forUser($this);
    }

    /**
     * Gets the user's about me.
     *
     * @return string
     */
    public function aboutMe() : string
    {
        return $this->pluck('aboutMe');
    }

    /**
     * Gets the user's account ID.
     *
     * @return string
     */
    public function accountId() : string
    {
        return $this->pluck('accountId');
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

        foreach ($this->pluck('avatarUrls') as $url)
        {
            $urls[$url['size']] = $url['avatarUrl'];
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
        return $this->pluck('blocking');
    }

    /**
     * Get the user's follower count.
     *
     * @return integer
     */
    public function followerCount() : int
    {
        return $this->pluck('followerCount');
    }

    /**
     * Check if the client is following the user.
     *
     * @return boolean
     */
    public function isFollowing() : bool
    {
        return $this->pluck('following');
    }

    /**
     * Check if the user is verified.
     *
     * @return boolean
     */
    public function isVerified() : bool
    {
        return $this->pluck('isOfficiallyVerified');
    }

    /**
     * Gets all the user's languages.
     *
     * @return array
     */
    public function languages() : array
    {
        return $this->pluck('languagesUsed');
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
        return $this->pluck('mutualFriendsCount');
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
        return $this->pluck('personalDetailSharing') !== 'no';
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
        return $this->pluck('friendRelation') === 'requesting';
    }

    /**
     * Checks if the user is currently online.
     *
     * @return boolean
     */
    public function isOnline() : bool
    {
        return $this->pluck('presences.0.onlineStatus') === 'online';
    }

    /**
     * Gets the user's current online ID.
     *
     * @return string
     */
    public function onlineId() : string
    {
        return $this->exact ? $this->onlineIdParameter : $this->pluck('onlineId');
    }

    /**
     * Checks if the user has PlayStation Plus.
     *
     * @return boolean
     */
    public function hasPlus() : bool
    {
        return $this->pluck('plus');
    }

    /**
     * Gets the user's profile info from the PlayStation API.
     *
     * @return object
     */
    public function fetch() : object
    {
        return $this->get('https://us-prof.np.community.playstation.net/userProfile/v1/users/' . $this->onlineIdParameter . '/profile2', [
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