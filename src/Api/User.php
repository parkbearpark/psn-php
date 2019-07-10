<?php

namespace Tustin\PlayStation\Api;

use Tustin\PlayStation\Client;
use Tustin\PlayStation\SessionType;

use Tustin\PlayStation\Resource\Image;
use Tustin\PlayStation\Resource\Audio;

use Tustin\PlayStation\Api\Session;
use Tustin\PlayStation\Api\Game;

use Tustin\PlayStation\Api\Community\Community;

use Tustin\PlayStation\Api\Messaging\Message;
use Tustin\PlayStation\Api\Messaging\MessageThread;

use Tustin\PlayStation\Api\Story\Story;

use Tustin\PlayStation\Api\Trophy\Trophy;
use Tustin\PlayStation\Api\Trophy\TrophyGroup;

class User extends AbstractApi 
{
    const USERS_ENDPOINT = 'https://us-prof.np.community.playstation.net/userProfile/v1/users/%s/';

    private $onlineId;
    private $onlineIdParameter;
    private $profile;
    private $isLoggedInUser;

    /**
     * Constructs a new User.
     *
     * @param Client $client
     * @param string $onlineId The online ID.
     */
    public function __construct(Client $client, string $onlineId = '')
    {
        parent::__construct($client);

        $this->onlineId = $onlineId;
        $this->onlineIdParameter = ($onlineId == '') ? 'me' : $onlineId;
        $this->isLoggedInUser = $this->onlineIdParameter == 'me';
    }

    /**
     * Gets the user's online ID.
     *
     * @return string
     */
    public function onlineId() : string
    {
        return $this->info()->onlineId;
    }

    /**
     * Gets the user's about me.
     *
     * @return string
     */
    public function aboutMe() : string
    {
        return $this->info()->aboutMe ?? "";
    }
    /**
     * Checks if logged in user is following the current user.
     *
     * @return boolean
     */
    public function following() : bool
    {
        return $this->info()->following;
    }
    /**
     * Gets the user's follower count.
     *
     * @return integer
     */
    public function followerCount() : int
    {
        return $this->info()->followerCount;
    }
    /**
     * Checks if the user is verified or not.
     *
     * @return boolean
     */
    public function verified() : bool
    {
        return $this->info()->isOfficiallyVerified;
    }
    /**
     * Gets the user's avatar URL.
     *
     * @return string
     */
    public function avatarUrl() : string
    {
        return $this->info()->avatarUrls[0]->avatarUrl;
    }
    /**
     * Gets the user's account ID.
     *
     * @return string
     */
    public function accountId() : string
    {
        return $this->info()->accountId;
    }
    
    /**
     * Checks if logged in user is friends with the current user.
     *
     * @return boolean
     */
    public function friend() : bool
    {
        return ($this->info()->friendRelation !== 'no');
    }
    /**
     * Checks if logged in user is close friends with the current user.
     *
     * @return boolean
     */
    public function closeFriend() : bool
    {
        return ($this->info()->personalDetailSharing !== 'no');
    }
    /**
     * Gets the last online date and time for the user.
     *
     * @return \DateTime|null
     */
    public function lastOnlineDate() : ?\DateTime
    {
        $isOnline = $this->info()->presences[0]->onlineStatus == "online";
        // They're online now, so just return the current DateTime.
        if ($isOnline) return new \DateTime();
        // If they don't have a DateTime, just return null.
        // This can happen if the User object was created using the onlineId string and not the profile data.
        // Sony only provides lastOnlineDate on the 'friends/profiles2' endpoint and not the individual userinfo endpoint.
        // This can be a TODO if in the future Sony decides to make that property available for that endpoint.
        // - Tustin 9/29/2018
        if (!isset($this->info()->presences[0]->lastOnlineDate)) return null;
        
        // I guess it's possible for lastOnlineDate to not exist, but that seems very unlikely.
        return new \DateTime($this->info()->presences[0]->lastOnlineDate);
    }

    /**
     * Gets the online ID parameter used for user requests.
     *
     * @return string
     */
    private function onlineIdParameter() : string
    {
        return $this->onlineIdParameter;
    }

    /**
     * Gets user's information.
     *
     * @return object
     */
    public function info() : \stdClass
    {
        if ($this->profile === null) {
            $this->profile = $this->client->get(sprintf(self::USERS_ENDPOINT . 'profile2', $this->onlineIdParameter), [
                'fields' => 'npId,onlineId,accountId,avatarUrls,plus,aboutMe,languagesUsed,trophySummary(@default,progress,earnedTrophies),isOfficiallyVerified,personalDetail(@default,profilePictureUrls),personalDetailSharing,personalDetailSharingRequestMessageFlag,primaryOnlineStatus,presences(@titleInfo,hasBroadcastData),friendRelation,requestMessageFlag,blocking,mutualFriendsCount,following,followerCount,friendsCount,followingUsersCount&avatarSizes=m,xl&profilePictureSizes=m,xl&languagesUsedLanguageSet=set3&psVitaTitleIcon=circled&titleIconSize=s'
            ])->profile;
        }

        return $this->profile;
    }

    /**
     * Add the user to friends list.
     *
     * Will always return false if called on the logged in user.
     * 
     * @param string $requestMessage Message to send with the request.
     * @return bool
     */
    public function add(string $requestMessage = null) : bool
    {
        if ($this->isLoggedInUser) {
            return false;
        }

        $data = ($requestMessage === null) ? new \stdClass() : [
            'requestMessage' => $requestMessage
        ];

        $this->postJson(sprintf(self::USERS_ENDPOINT . 'friendList/%s', $this->client->onlineId(), $this->onlineId()), $data);

        return true;
    }

    /**
     * Remove the User from friends list.
     *
     * Will always return false if called on the logged in user.
     * 
     * @return bool
     */
    public function remove() : bool
    {
        if ($this->isLoggedInUser) {
            return false;
        }

        $this->delete(sprintf(self::USERS_ENDPOINT . 'friendList/%s', $this->client->onlineId(), $this->onlineId()));
        
        return true;
    }

    /**
     * Block the current user.
     * 
     * Will always return false if called on the logged in user.
     *
     * @return bool
     */
    public function block() : bool
    {
        if ($this->isLoggedInUser) {
            return false;
        }

        $this->post(sprintf(self::USERS_ENDPOINT . 'blockList/%s', $this->client->onlineId(), $this->onlineId()), null);

        return true;
    }

    /**
     * Unblock the current user.
     * 
     * Will always return false if called on the logged in user.
     *
     * @return bool
     */
    public function unblock() : bool
    {
        if ($this->isLoggedInUser) {
            return false;
        }

        $this->delete(sprintf(self::USERS_ENDPOINT . 'blockList/%s', $this->client->onlineId(), $this->onlineId()));

        return true;
    }

    /**
     * Gets the user's friends.
     *
     * @param string $sort Order to return friends in (onlineStatus | name-onlineId)
     * @param int $offset Where to start
     * @param int $limit How many friends to return
     * @return array Array of \Tustin\PlayStation\Api\User
     */
    public function friends($sort = 'onlineStatus', $offset = 0, $limit = 36) : array
    {
        $friendsResult = [];

        $friends = $this->client->get(sprintf(self::USERS_ENDPOINT . 'friends/profiles2', $this->onlineIdParameter()), [
            'fields' => 'onlineId',
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort
        ]);
        
        foreach ($friends->profiles as $friend) {
            $friendsResult[] = new self($this->client, $friend->onlineId);
        }

        return $friendsResult;
    }

    /**
     * Gets the user's games played.
     *
     * @return array Array of \Tustin\PlayStation\Api\Game
     */
    public function games($limit = 100) : array
    {
        $returnGames = [];

        $games = $this->fetchPlayedGames($limit);

        if ($games->size === 0) {
            return $returnGames;
        }

        foreach ($games->titles as $game) {
            $returnGames[] = new Game($this->client, $game->titleId, $this);
        }

        return $returnGames;
    }

    /**
     * @param int $limit
     * @return object
     */

     // TODO: Clean this up.
     // - Tustin 7 July 2019
    public function fetchPlayedGames($limit) : \stdClass
    {
        return $this->client->get(sprintf(Game::GAME_ENDPOINT . 'users/%s/titles', $this->onlineId()), [
            'type'  => 'played',
            'app'   => 'richProfile', // ??
            'sort'  => '-lastPlayedDate',
            'limit' => $limit,
            'iw'    => 240, // Size of game image width
            'ih'    => 240  // Size of game image height
        ]);
    }

    /**
     * Send a Message to the User.
     *
     * @param string $message Message to send.
     * @return \Tustin\PlayStation\Api\Messaging\Message|null
     */
    public function sendMessage(string $message) : ?Message 
    {
        $thread = $this->messageGroup();

        if ($thread === null) {
            return null;
        }

        return $thread->sendMessage($message);
    }

    /**
     * Send an image Message to the User.
     *
     * @param string $imageContents Raw bytes of the image.
     * @return \Tustin\PlayStation\Api\Messaging\Message|null
     */
    public function sendImage(Image $image) : ?Message
    {
        $thread = $this->messageGroup();

        if ($thread === null) {
            return null;
        }
        
        return $thread->sendImage($imageContents);
    }

    /**
     * Send an audio Message to the User.
     *
     * @param string $audioContents Raw bytes of the audio.
     * @param int $audioLengthSeconds Length of audio file (in seconds).
     * @return \Tustin\PlayStation\Api\Messaging\Message|null
     */
    public function sendAudio(string $audioContents, int $audioLengthSeconds) : ?Message
    {
        $thread = $this->messageGroup();
        
        if ($thread === null) {
            return null;
        }
        
        return $thread->sendAudio($audioContents, $audioLengthSeconds);
    }

    /**
     * Get all message threads containing this user.
     * 
     * If the user instance is of the logged in user, this will return ALL message threads.
     *
     * @return array Array of \Tustin\PlayStation\Api\Messaging\MessageThread
     */
    public function messageThreads() : array
    {
        $returnThreads = [];

        $threads = $this->client->get(MessageThread::MESSAGE_THREAD_ENDPOINT . 'threads', [
            'fields' => 'threadMembers'
        ]);

        if (empty($threads->threads)) {
            return $returnThreads;
        }

        if (!$this->isLoggedInUser) {
            $threads->threads = array_filter($threads->threads, function($thread) {

                if (!isset($thread->threadMembers) || count($thread->threadMembers) <= 1) {
                    return false;
                }

                // We're going to use a foreach loop for this so we can return once we find the user.
                // Otherwise using some callback function might be an issue if a thread has lots of members.
                // Just wastes time!
                foreach ($thread->threadMembers as $member) {
                    if ($member->onlineId === $this->onlineId()) {
                        return true;
                    }
                }

                return false;
            });
        }

        foreach ($threads->threads as $thread) {
            $returnThreads[] = new MessageThread($this->client, $thread->threadId);
        }

        return $returnThreads;
    }

    /**
     * Get message thread with just the logged in account and the current user.
     *
     * @return \Tustin\PlayStation\Api\Messaging\MessageThread|null
     */
    public function privateMessageThread() : ?MessageThread
    {
        $threads = $this->messageThreads();

        if (count($threads) === 0) {
            return null;
        }

        foreach ($threads as $thread) {
            if ($thread->memberCount() === 2) {
                return $thread;
            }
        }

        return null;
    }

    /**
     * Returns an instance of the party session (if applicable).
     *
     * @return \Tustin\PlayStation\Api\Session|null
     */
    public function partySession() : ?Session 
    {
        $sessions = $this->filterSessions(SessionType::Party);

        return $sessions[0] ?? null;
    }

    /**
     * Returns an instance of the game session (if applicable).
     *
     * @return \Tustin\PlayStation\Api\Session|null
     */
    public function gameSession() : ?Session
    {
        $sessions = $this->filterSessions(SessionType::Game);

        return $sessions[0] ?? null;
   
    }


    /**
     * Gets the user's activity feed.
     * 
     * @param int $page Which page to get data from.
     * @param bool $includeComments Should the story comments be returned?
     * @param int $offset How many stories to skip.
     * @param int $blockSize How many stories to return.
     * @return array Array of \Tustin\PlayStation\Api\Story\Story
     */
    public function feed(int $page = 0, bool $includeComments = true, int $offset = 0, int $blockSize = 10) : array
    {
        $returnActivity = [];

        $activity = $this->client->get(sprintf(Story::ACTIVITY_ENDPOINT . 'v2/users/%s/feed/%d', $this->onlineId(), $page), [
            'includeComments' => $includeComments,
            'offset' => $offset,
            'blockSize' => $blockSize
        ]);

        if (empty($activity->feed)) {
            return $returnActivity;
        }

        foreach ($activity->feed as $story) {
            // Some stories might just be a collection of similiar stories (like trophy unlocks)
            // These stories can't be interacted with like other stories, so we need to grab all the condensed stories.
            if (isset($story->condensedStories)) {
                foreach ($story->condensedStories as $condensed) {
                    $returnActivity[] = new Story($this->client, $condensed, $this);
                }
            } else {
                $returnActivity[] = new Story($this->client, $story, $this);
            }
        }

        return $returnActivity;
    }

    /**
     * Get all communities the user is in.
     * 
     * @param string $sort Not totally documented yet. 
     * @return array Array of \Tustin\PlayStation\Api\Community
     */
    public function communities(string $sort = 'common') : array
    {
        $returnCommunities = [];

        $communities = $this->client->get(Community::COMMUNITY_ENDPOINT . 'communities', [
            'fields' => 'backgroundImage,description,id,isCommon,members,name,profileImage,role,unreadMessageCount,sessions,timezoneUtcOffset,language,titleName',
            'includeFields' => 'gameSessions,timezoneUtcOffset,parties',
            'sort' => $sort,
            'onlineId' => $this->onlineId()
        ]);

        if (!isset($communities->communities) || empty($communities->communities) ||  $communities->size === 0) {
            return $returnCommunities;
        }
        
        foreach ($communities->communities as $community) {
            $returnCommunities[] = new Community($this->client, $community->id);
        }
        
        return $returnCommunities;
    }

    /**
     * Gets (or creates) the message thread with just the logged in account and the current User.
     *
     * @return \Tustin\PlayStation\Api\Messaging\MessageThread|null
     */
    private function messageThread() : ?MessageThread
    {
        if ($this->isLoggedInUser) {
            return null;
        }

        $thread = $this->privateMessageThread();

        if ($thread === null) {
            // If we couldn't find an existing message thread, let's make one.
            $data = [
                'threadDetail' => [
                    'threadMembers' => [
                        [
                            'onlineId' => $this->onlineId()
                        ],
                        [
                            'onlineId' => $this->client->onlineId()
                        ]
                    ]
                ]
            ];

            $parameters = [
                [
                    'name' => 'threadDetail',
                    'contents' => json_encode($data, JSON_PRETTY_PRINT),
                    'headers' => [
                        'Content-Type' => 'application/json; charset=utf-8'
                    ]
                ]
            ];

            $response = $this->postMultiPart(MessageThread::MESSAGE_THREAD_ENDPOINT . 'threads/', $parameters);

            $thread = new MessageThread($this->client, $response->threadId);
        }

        return $thread;
    }

    /**
     * Filter user's sessions by SessionType flag.
     *
     * @param int $type SessionType flag.
     * @return array Array of \Tustin\PlayStation\Api\Session.
     */
    private function filterSessions(int $type) : array
    {
        $sessions = $this->sessions();
        
        $filteredSession = array_filter($sessions, function($session) use ($type) {
            if ($session->getTitleType() & $type) return $session;
        });

        return $filteredSession;
    }

    /**
     * Gets all the User's active Sessions.
     *
     * @return array Array of \Tustin\PlayStation\Api\Session.
     */
    private function sessions() : array
    {
        $returnSessions = [];

        $sessions = $this->client->get(sprintf(Session::SESSION_ENDPOINT, $this->onlineId()), [
            'fields' => '@default,npTitleDetail,npTitleDetail.platform,sessionName,sessionCreateTimestamp,availablePlatforms,members,memberCount,sessionMaxUser',
            'titleIconSize' => 's',
            'npLanguage' => 'en'
        ]);

        if ($sessions->size === 0) {
            return $returnSessions;
        }

        // Multiple sessions could be used if the user is in a party while playing a game.
        foreach ($sessions->sessions as $session) {
            $returnSessions[] = new Session($this->client, $session);
        }

        return $returnSessions;
    }
}