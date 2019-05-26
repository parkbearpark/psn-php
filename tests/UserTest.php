<?php

namespace PlayStation\Tests;

use PlayStation\Client;

class UserTest extends \PHPUnit\Framework\TestCase
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
        self::$client = new Client([ 'verify' => false, 'proxy' => '127.0.0.1:8888']);

        $refreshToken = getenv('PSN_PHP_REFRESH_TOKEN');

        self::$client->login($refreshToken);

        self::$loggedInUser = self::$client->user();

        self::$testUser = self::$client->user('Test');

        self::$tustinUser = self::$client->user('tustin25');
    }

    public function testInvalidOnlineId()
    {
        $user = self::$client->user('something that should $$ never happen!!!');

        $this->expectException('\PlayStation\Exception\NotFoundException');

        $user->games();
    }

    public function testValidOnlineId()
    {
        $this->assertEquals(self::$testUser->onlineId(), 'test');
    }

    public function testAreTestAndIFriends()
    {
        $this->assertFalse(self::$testUser->friend());
    }

    public function testAreTestAndICloseFriends()
    {
        $this->assertFalse(self::$testUser->closeFriend());
    }

    public function testAmIFollowingTest()
    {
        $this->assertFalse(self::$testUser->following());
    }

    public function testAmIVerified()
    {
        $this->assertFalse(self::$loggedInUser->verified());
    }

    public function testGetMyAboutMe()
    {
        $this->assertEquals(self::$loggedInUser->aboutMe(), 'Hello psn-php!');
    }

    public function testIsMyAvatarTheDefaultAvatar()
    {
        $this->assertContains('Defaultavatar', self::$loggedInUser->avatarUrl());
    }

    public function testGetMyFriends()
    {
        $friends = self::$loggedInUser->friends();

        $this->assertInternalType('array', $friends);

        // This account is friends with my main account.
        $this->assertEquals(count($friends), 1);
    }

    public function testTryToGetUsersFriendsWithPrivacySettingsEnabled()
    {
        $this->expectException('\PlayStation\Exception\AccessDeniedException');

        self::$testUser->friends();
    }
    
    public function testGetUsersFriendsWithPrivacySettingsDisabled()
    {
        $friends = self::$tustinUser->friends();

        $this->assertInternalType('array', $friends);
        $this->assertEquals(count($friends), 36);

        $friend = $friends[0];

        $this->assertInstanceOf('\PlayStation\Api\User', $friend);
    }

    public function testGetMyGames()
    {
        $games = self::$loggedInUser->games();

        $this->assertInternalType('array', $games);

        // This user shouldn't have any games played.
        $this->assertEquals(count($games), 0);
    }

    public function testGetUsersGamesWithPrivacySettingsEnabled()
    {
        $this->expectException('\PlayStation\Exception\AccessDeniedException');

        self::$testUser->games();
    }

    public function testGetUsersGamesWithPrivacySettingsDisabled()
    {
        $games = self::$tustinUser->games();

        $this->assertInternalType('array', $games);
        $this->assertEquals(count($games), 100);

        $game = $games[0];

        $this->assertInstanceOf('\PlayStation\Api\Game', $game);
    }
}