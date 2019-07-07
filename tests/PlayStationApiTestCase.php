<?php

namespace Tustin\PlayStation\Tests;

use Tustin\PlayStation\Client;

class PlayStationApiTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * PlayStation Client
     *
     * @var PlayStation\Client
     */
    protected static $client;

    /**
     * The logged in user
     *
     * @var PlayStation\Api\User
     */
    protected static $loggedInUser;

    /**
     * Test User
     *
     * @var PlayStation\Api\User
     */
    protected static $testUser;

    /**
     * My PSN account
     * 
     * My account shouldn't have any privacy settings enabled which should allow all API calls to succeed.
     *
     * @var PlayStation\Api\User
     */
    protected static $tustinUser;

    public static function setUpBeforeClass()
    {
        self::$client = new Client(['verify' => false, 'proxy' => '127.0.0.1:8888']);

        $refreshToken = getenv('PSN_PHP_REFRESH_TOKEN');

        self::$client->login($refreshToken);

        self::$loggedInUser = self::$client->user();

        self::$testUser = self::$client->user('Test');

        self::$tustinUser = self::$client->user('tustin25');
    }
}