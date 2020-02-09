<?php

namespace Tustin\PlayStation\Tests;

use Tustin\PlayStation\Client;
use Tustin\Haste\Exception\ApiException;

class AuthenticationTest extends \PHPUnit\Framework\TestCase
{
    public function testRefreshTokenEnvironment()
    {
        $refreshToken = getenv('PSN_PHP_REFRESH_TOKEN');
        $this->assertNotEmpty($refreshToken, 'Missing refresh token environment variable.');
    }

    public function testNpssoEnvironment()
    {
        $npsso = getenv('PSN_PHP_NPSSO');
        $this->assertNotEmpty($npsso, 'Missing npsso environment variable.');
    }

    public function testInvalidRefreshToken()
    {
        $this->expectException(ApiException::class);
        (new Client)->loginWithRefreshToken('deadbeef');
    }

    public function testInvalidNpssoLogin()
    {
        $this->expectException(ApiException::class);
        (new Client)->loginWithNpsso('some-fake-npsso-code');
    }

    /**
     * @depends testRefreshTokenEnvironment
     */
    public function testLoginWithRefreshToken()
    {
        $refreshToken = getenv('PSN_PHP_REFRESH_TOKEN');
        
        $client = new Client();
        $client->loginWithRefreshToken($refreshToken);

        $this->assertNotNull($client->accessToken());
    }

    /**
     * @depends testNpssoEnvironment
     */
    public function testLoginWithNpssoToken()
    {
        $npsso = getenv('PSN_PHP_NPSSO');
        
        $client = new Client();

        $client->loginWithNpsso($npsso);

        $this->assertNotNull($client->accessToken());
    }
}