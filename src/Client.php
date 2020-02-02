<?php

namespace Tustin\PlayStation;

use Carbon\Carbon;

use Tustin\Haste\AbstractClient;

use Tustin\PlayStation\Http\HttpClient;
use Tustin\PlayStation\Api\MessageThread;
use Tustin\PlayStation\Http\ResponseParser;
use Tustin\PlayStation\Http\TokenMiddleware;
use Tustin\PlayStation\Http\ResponseHandlerMiddleware;
use Tustin\Haste\Http\Middleware\AuthenticationMiddleware;

class Client extends AbstractClient
{
    private const VERSION = 'dev-3.0.0';
    // The client id and client secret are for the iOS PlayStation app.
    // These cannot be generated at this time; therefore, we have to use their permissions scopes as well.
    private const CLIENT_ID     = 'ebee17ac-99fd-487c-9b1e-18ef50c39ab5';
    private const CLIENT_SECRET = 'e4Ru_s*LrL4_B2BD';
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

    private $accessToken;

    private $refreshToken;
    
    private $expiresAt;

    public function __construct(array $guzzleOptions = [])
    {
        // We can't really use a base_uri here because Sony sucks.
        $guzzleOptions['allow_redirects'] = false;
        $guzzleOptions['headers']['User-Agent'] = 'psn-php/' . self::VERSION;
 
        parent::__construct($guzzleOptions);
    }

    /**
     * Login using a npsso token.
     *
     * @param string $npsso
     * @return void
     */
    public function loginWithNpsso(string $npsso) : void
    {
        $response = $this->post('https://auth.api.sonyentertainmentnetwork.com/2.0/oauth/token', [
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'scope' => implode(' ', self::SCOPES),
            'grant_type' => 'sso_cookie',
        ], [ 'Cookie' => 'npsso=' . $npsso ]);

        $this->postLogin($response);
    }

    /**
     * Login using an existing refresh token.
     *
     * @param string $refreshToken
     * @return void
     */
    public function loginWithRefreshToken(string $refreshToken) : void
    {
        $response = $this->post('https://auth.api.sonyentertainmentnetwork.com/2.0/oauth/token', [
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'scope' => implode(' ', self::SCOPES),
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ]);

        $this->postLogin($response);
    }

    /**
     * Sets information from a login response.
     *
     * @param object $response
     * @return void
     */
    private function postLogin(object $response) : void
    {
        $this->setAccessToken($response->access_token);
        $this->refreshToken = $response->refresh_token;
        $this->expiresAt = Carbon::now()->addSeconds($response->expires_in);
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

        $this->pushAuthenticationMiddleware(new AuthenticationMiddleware([
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ]));
    }
    
    public function getAccessToken() : string
    {
        return $this->accessToken;
    }

    public function getRefreshToken() : string
    {
        return $this->refreshToken;
    }

    public function getExpireDate() : \Carbon
    {
        return $this->expiresAt;
    }

    public function __call(string $method, array $parameters) : object
    {
        $class = "\\Tustin\\PlayStation\\Api\\" . ucwords($method);

        if (class_exists($class))
        {
            return new $class($this->httpClient);
        }

        throw new \BadMethodCallException("'{$method}' does not exist.");
    }
}