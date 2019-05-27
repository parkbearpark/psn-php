<?php

namespace PlayStation\Tests;

use PlayStation\Client;

class AuthenticationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * PlayStation Client
     *
     * @var PlayStation\Client;
     */
    protected $client;

    protected function setUp()
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
        $this->expectException('\PlayStation\Exception\PlayStationApiException');
        $this->client->login('deadbeef');
    }

    public function testInvalidTwoFactorLogin()
    {
        $this->expectException('\PlayStation\Exception\PlayStationApiException');
        $this->client->login('abc', 6969);
    }

    /**
     * @depends testEnvironment
     */
    public function testLoginWithRefreshToken()
    {
        $refreshToken = getenv('PSN_PHP_REFRESH_TOKEN');
        
        $this->client->login($refreshToken);

        $this->assertEquals($this->client->onlineId(), 'speedy424key');
    }
}