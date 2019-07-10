<?php

namespace Tustin\PlayStation\Tests;

use Tustin\PlayStation\Client;

class AuthenticationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * PlayStation Client
     *
     * @var PlayStation\Client;
     */
    protected $client;

    protected function setUp() : void
    {
        $this->client = new Client();
    }

    public function testEnvironment()
    {
        $refreshToken = getenv('PSN_PHP_REFRESH_TOKEN');
        $this->assertNotEmpty($refreshToken, 'Missing refresh token for PSN API.');
    }

    public function testInvalidRefreshToken()
    {
        $this->expectException('\Tustin\PlayStation\Exception\PlayStationApiException');
        $this->client->loginWithRefreshToken('deadbeef');
    }

    public function testInvalidTwoFactorLogin()
    {
        $this->expectException('\Tustin\PlayStation\Exception\PlayStationApiException');
        $this->client->login('abc', 6969);
    }

    /**
     * @depends testEnvironment
     */
    public function testLoginWithRefreshToken()
    {
        $refreshToken = getenv('PSN_PHP_REFRESH_TOKEN');
        
        $this->client->loginWithRefreshToken($refreshToken);

        $this->assertEquals($this->client->onlineId(), 'speedy424key');
    }
}