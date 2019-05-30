<?php

namespace PlayStation;

use PlayStation\Api\MessageThread;
use PlayStation\Api\User;
use PlayStation\Api\Game;
use PlayStation\Api\Community;

use PlayStation\Http\HttpClient;
use PlayStation\Http\ResponseParser;
use PlayStation\Http\TokenMiddleware;
use PlayStation\Http\ResponseHandlerMiddleware;

use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;

class Client extends HttpClient
{
    const AUTH_API      = 'https://auth.api.sonyentertainmentnetwork.com/2.0/';

    private const CLIENT_ID     = 'ebee17ac-99fd-487c-9b1e-18ef50c39ab5';
    private const CLIENT_SECRET = 'e4Ru_s*LrL4_B2BD';
    private const DUID          = '0000000d00040080027BC1C3FBB84112BFC9A4300A78E96A';
    private const SCOPE         = 'kamaji:get_players_met kamaji:get_account_hash kamaji:activity_feed_submit_feed_story kamaji:activity_feed_internal_feed_submit_story kamaji:activity_feed_get_news_feed kamaji:communities kamaji:game_list kamaji:ugc:distributor oauth:manage_device_usercodes psn:sceapp user:account.profile.get user:account.attributes.validate user:account.settings.privacy.get kamaji:activity_feed_set_feed_privacy kamaji:satchel kamaji:satchel_delete user:account.profile.update';

    private $onlineId;
    private $messageThreads;
    private $options;

    private $accessToken;
    private $refreshToken;
    private $expiresIn;

    /**
     * @param array $options Guzzle Options
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['handler']))
        {
            $options['handler'] = HandlerStack::create();
        }

        $options['allow_redirects'] = false;

        $this->options = $options;

        $this->httpClient = new \GuzzleHttp\Client($this->options);

        $config  = $this->httpClient->getConfig();
        $handler = $config['handler'];

        $handler->push(
            Middleware::mapResponse(
                new ResponseHandlerMiddleware
            )
        );
    }
    
    /**
     * Login to PlayStation network using a refresh token or 2FA.
     *
     * @param string $ticketUuidOrRefreshToken Ticket UUID for 2FA, or the refresh token.
     * @param string $code 2FA code sent to your device (ignore if using refresh token).
     * @return void
     */
    public function login(string $ticketUuidOrRefreshToken, string $code = null) : void
    {
        if ($code === null) {
            $response = $this->post(self::AUTH_API . 'oauth/token', [
                "app_context" => "inapp_ios",
                "client_id" => self::CLIENT_ID,
                "client_secret" => self::CLIENT_SECRET,
                "refresh_token" => $ticketUuidOrRefreshToken,
                "duid" => self::DUID,
                "grant_type" => "refresh_token",
                "scope" => self::SCOPE
            ]);
        } else {
            $response = $this->post(self::AUTH_API . 'ssocookie', [
                'authentication_type' => 'two_step',
                'ticket_uuid' => $ticketUuidOrRefreshToken,
                'code' => $code,
                'client_id' => self::CLIENT_ID
            ]);

            $npsso = $response->npsso;

            $response = $this->get(self::AUTH_API . 'oauth/authorize', [
                'duid' => self::DUID,
                'client_id' => self::CLIENT_ID,
                'response_type' => 'code',
                'scope' => self::SCOPE,
                'redirect_uri' => 'com.playstation.PlayStationApp://redirect'
            ], [
                'Cookie' => 'npsso=' . $npsso
            ]);

            // Get the last response provided by Guzzle to grab a header value.
            $grant = $this->lastResponse()->getHeaderLine('X-NP-GRANT-CODE');

            if (empty($grant)) {
                throw new \Exception('Unable to get X-NP-GRANT-CODE');
            }

            $response = $this->post(self::AUTH_API . 'oauth/token', [
                'client_id' => self::CLIENT_ID,
                'client_secret' => self::CLIENT_SECRET,
                'duid' => self::DUID,
                'scope' => self::SCOPE,
                'redirect_uri' => 'com.playstation.PlayStationApp://redirect',
                'code' => $grant,
                'grant_type' => 'authorization_code'
            ]);

        }

        $this->accessToken = $response->access_token;
        $this->refreshToken = $response->refresh_token;
        $this->expiresIn = $response->expires_in;

        $this->pushTokenMiddleware($this->accessToken);
    }

    /**
     * Pushes TokenMiddleware onto the HandlerStack with the access token.
     *
     * @param string $accessToken PlayStation access token.
     * @return void
     */
    private function pushTokenMiddleware(string $accessToken) : void
    {
        $config  = $this->httpClient->getConfig();
        $handler = $config['handler'];

        $handler->push(
            Middleware::mapRequest(
                new TokenMiddleware($accessToken)
            )
        );
    }

    /**
     * Access the PlayStation API using an existing access token.
     *
     * @param string $accessToken
     * @return void
     */
    public function setAccessToken(string $accessToken) : void
    {
        $this->accessToken = $accessToken;

        $this->pushTokenMiddleware($this->accessToken);
    }

    /**
     * Gets the logged in user's onlineId.
     *
     * @return string
     */
    public function onlineId() : string
    {
        if ($this->onlineId === null) {
            $response = $this->get(sprintf(User::USERS_ENDPOINT . 'profile2', 'me'), [
                'fields' => 'onlineId'
            ]);

            $this->onlineId = $response->profile->onlineId;
        }
        return $this->onlineId;
    }

    /**
     * Gets the access token.
     *
     * @return string
     */
    public function accessToken() : string
    {
        return $this->accessToken;
    }

    /**
     * Gets the refresh token.
     *
     * @return string
     */
    public function refreshToken() : string
    {
        return $this->refreshToken;
    }

    /**
     * Gets the access token expire DateTime.
     *
     * @return \DateTime
     */
    public function expireDate() : \DateTime
    {
        return new \DateTime(sprintf('+%d seconds', $this->expiresIn));
    }

    /**
     * Gets all MessageThreads for the current Client.
     *
     * @param integer $offset Where to start.
     * @param integer $limit Amount of threads.
     * @return object
     */
    public function messageThreads(int $offset = 0, int $limit = 20) : \stdClass
    {
        if ($this->messageThreads === null) {
            $response = $this->get(MessageThread::MESSAGE_THREAD_ENDPOINT . 'threads/', [
                'fields' => 'threadMembers',
                'limit' => $limit,
                'offset' => $offset,
                'sinceReceivedDate' => '1970-01-01T00:00:00Z' // Don't hardcode
            ]);

            $this->messageThreads = $response;
        }
        return $this->messageThreads;
    }

    /**
     * Creates a new User object.
     *
     * @param string $onlineId The User's onlineId (null to get current User's account).
     * @return PlayStation\Api\User
     */
    public function user(string $onlineId = '') : User
    {
        return new User($this, $onlineId);
    }

    /**
     * Find a game by it's title ID and return a new Game object.
     *
     * @param string $titleId The Game's title ID
     * @return PlayStation\Api\Game
     */
    public function game(string $titleId) : Game
    {
        return new Game($this, $titleId);
    }

    /**
     * Get or create a Community.
     *
     * @param string $communityIdOrName Community ID or the name of the new community.
     * @param string $type 
     * @param string $titleId Game to associate your Community with.
     * @return PlayStation\Api\Community
     */
    public function community(string $communityIdOrName, string $type = '', string $titleId = '') : Community
    {
        if (!empty($type) && !empty($titleId)) {
            // Create the Community.
            // $response = $this->postJson(Community::COMMUNITY_ENDPOINT . 'communities?action=create', [
            //     'name' => $communityIdOrName,
            //     'type' => $type,
            //     'titleId' => $titleId
            // ]);

            $communityIdOrName = $response->id;
        }

        return new Community($this, $communityIdOrName);
    }
}