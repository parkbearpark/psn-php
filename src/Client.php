<?php

namespace Tustin\PlayStation;

use Tustin\PlayStation\Api\MessageThread;
use Tustin\PlayStation\Api\User;
use Tustin\PlayStation\Api\Game;
use Tustin\PlayStation\Api\Community;

use Tustin\PlayStation\Http\HttpClient;
use Tustin\PlayStation\Http\ResponseParser;
use Tustin\PlayStation\Http\TokenMiddleware;
use Tustin\PlayStation\Http\ResponseHandlerMiddleware;

use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;

class Client extends HttpClient
{
    const AUTH_API      = 'https://auth.api.sonyentertainmentnetwork.com/2.0/';

    // The client id and client secret are for the iOS PlayStation app.
    // These cannot be generated at this time; therefore, we have to use their permissions scopes as well.
    private const CLIENT_ID     = 'ebee17ac-99fd-487c-9b1e-18ef50c39ab5';
    private const CLIENT_SECRET = 'e4Ru_s*LrL4_B2BD';
    private const DUID          = '0000000d00040080027BC1C3FBB84112BFC9A4300A78E96A';
    private const SCOPES = [
        'kamaji:activity_feed_get_news_feed',
        'kamaji:activity_feed_internal_feed_submit_story',
        'kamaji:activity_feed_set_feed_privacy',
        'kamaji:activity_feed_submit_feed_story',
        'kamaji:communities',
        'kamaji:game_list',
        'kamaji:get_account_hash',
        'kamaji:get_players_met',
        'kamaji:satchel',
        'kamaji:satchel_delete',
        'kamaji:ugc:distributor',
        'oauth:manage_device_usercodes',
        'psn:sceapp',
        'user:account.attributes.validate',
        'user:account.profile.get',
        'user:account.profile.update',
        'user:account.settings.privacy.get'
    ];
    private const REDIRECT_URL = 'com.playstation.PlayStationApp://redirect';

    private $guzzleOptions;

    private $accessToken;
    private $refreshToken;
    private $expiresIn;

    private $onlineId;
    private $messageThreads;

    /**
     * @param array $guzzleOptions Guzzle options
     */
    public function __construct(array $guzzleOptions = [])
    {
        if (!isset($guzzleOptions['handler']))
        {
            $guzzleOptions['handler'] = HandlerStack::create();
        }

        $guzzleOptions['allow_redirects'] = false;

        $this->guzzleOptions = $guzzleOptions;

        $this->httpClient = new \GuzzleHttp\Client($this->guzzleOptions);

        $config  = $this->httpClient->getConfig();
        $handler = $config['handler'];

        $handler->push(
            Middleware::mapResponse(
                new ResponseHandlerMiddleware
            )
        );
    }

    /**
     * Create a new Client instance.
     *
     * @param array $guzzleOptions Guzzle options
     * @return \Tustin\PlayStation\Client
     */
    public static function create(array $guzzleOptions = []) : \Tustin\PlayStation\Client
    {
        return new static($guzzleOptions);
    }
    
    /**
     * Login to the PlayStation Network using information from a 2FA login request on the official Sony website.
     * 
     * This should be done for the initial login. Afterwards, make sure you save the refresh token and use loginWithRefreshToken instead.
     *
     * @param string $ticketUuid Ticket UUID from the 2FA login request.
     * @param string $code 2FA code sent to your device.
     * @return void
     */
    public function login(string $ticketUuid, string $code) : void
    {
        // Get the NPSSO cookie that is needed for the NP grant code.
        $response = $this->post(self::AUTH_API . 'ssocookie', [
            'authentication_type' => 'two_step',
            'ticket_uuid' => $ticketUuid,
            'code' => $code,
            'client_id' => self::CLIENT_ID
        ]);

        $npsso = $response->npsso;

        // Authorize the client and get the NP grant code.
        $response = $this->get(self::AUTH_API . 'oauth/authorize', [
            'duid' => self::DUID,
            'client_id' => self::CLIENT_ID,
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPE),
            'redirect_uri' => self::REDIRECT_URL
        ], [
            'Cookie' => 'npsso=' . $npsso
        ]);

        // Get the last response provided by Guzzle to grab a header value.
        $grant = $this->lastResponse()->getHeaderLine('X-NP-GRANT-CODE');

        if (empty($grant))
        {
            throw new \Exception('Unable to get X-NP-GRANT-CODE');
        }

        // Use the NP grant code to get OAuth tokens.
        $response = $this->post(self::AUTH_API . 'oauth/token', [
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'duid' => self::DUID,
            'scope' => implode(' ', self::SCOPES),
            'redirect_uri' => self::REDIRECT_URL,
            'code' => $grant,
            'grant_type' => 'authorization_code'
        ]);

        $this->postLogin($response);
    }

    /**
     * Login using an existing refresh token.
     * 
     * Use this for quick logins after you've logged in once with the 2FA login method.
     *
     * @param string $refreshToken The refresh token.
     * @return void
     */
    public function loginWithRefreshToken(string $refreshToken) : void
    {
        $response = $this->post(self::AUTH_API . 'oauth/token', [
            'app_context' => 'inapp_ios',
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'refresh_token' => $ticketUuidOrRefreshToken,
            'duid' => self::DUID,
            'grant_type' => 'refresh_token',
            'scope' => self::SCOPE
        ]);

        $this->postLogin($response);
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
     * Sets information from a login response.
     *
     * @param object $response The login response.
     * @return void
     */
    private function postLogin(object $response) : void
    {
        $this->accessToken = $response->access_token;
        $this->refreshToken = $response->refresh_token;
        $this->expiresIn = $response->expires_in;

        $this->pushTokenMiddleware($this->accessToken);
    }
    
    /**
     * Pushes TokenMiddleware onto the HandlerStack with the access token.
     *
     * @param string $accessToken PlayStation OAuth access token.
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
     * Gets the logged in user's online ID.
     *
     * @return string
     */
    public function onlineId() : string
    {
        if ($this->onlineId === null)
        {
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
     * @param int $offset Where to start.
     * @param int $limit Amount of threads.
     * @return \stdClass
     */
    public function messageThreads(int $offset = 0, int $limit = 20) : \stdClass
    {
        if ($this->messageThreads === null)
        {
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
     * Gets a User by their Online ID.
     *
     * @param string $onlineId The user's online ID (leave empty for the logged in user's account)
     * @return \Tustin\PlayStation\Api\User
     */
    public function user(string $onlineId = '') : User
    {
        return new User($this, $onlineId);
    }

    /**
     * Find a game by it's title ID and return a new Game object.
     *
     * @param string $titleId The Game's title ID
     * @return \Tustin\PlayStation\Api\Game
     */
    public function game(string $titleId) : Game
    {
        return new Game($this, $titleId);
    }

    /**
     * Gets a Community.
     *
     * @param string $communityId Community ID
     * @return \Tustin\PlayStation\Api\Community
     */
    public function community(string $communityId) : Community
    {
        return new Community($this, $communityId);
    }
}