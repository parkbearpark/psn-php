<?php

namespace Tustin\PlayStation\Tests;

use Tustin\PlayStation\Client;

class PlayStationApiTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * PlayStation Client
     *
     * @var Tustin\PlayStation\Client
     */
    protected static $client;

    /**
     * The logged in user
     *
     * @var Tustin\PlayStation\Api\User
     */
    protected static $loggedInUser;

    /**
     * Test User
     *
     * @var Tustin\PlayStation\Api\User
     */
    protected static $testUser;

    /**
     * My PSN account
     * 
     * My account shouldn't have any privacy settings enabled which should allow all API calls to succeed.
     *
     * @var Tustin\PlayStation\Api\User
     */
    protected static $tustinUser;

    public static function setUpBeforeClass() : void
    {
        self::$client = new Client(['verify' => false, 'proxy' => '127.0.0.1:8888']);

        $npsso = getenv('PSN_PHP_NPSSO');

        self::$client->loginWithNpsso($npsso);

        self::$loggedInUser = self::$client->users()->me();

        self::$testUser = self::$client->users()->find('Test');

        self::$tustinUser = self::$client->users()->find('tustin25');
    }
}