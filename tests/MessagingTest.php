<?php

namespace PlayStation\Tests;

use PlayStation\Client;
use PlayStation\Api\MessageThread;
use PlayStation\Api\Message;

class MessagingTest extends \PHPUnit\Framework\TestCase
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

    public function testGetMessageThreadsIAmIn()
    {
        $threads = self::$loggedInUser->messageThreads();

        $this->assertInternalType('array', $threads);

        $this->assertGreaterThanOrEqual(count($threads), 1);

        $thread = $threads[0];

        $this->assertInstanceOf('\PlayStation\Api\MessageThread', $thread);
    }

    public function testTryToGetPrivateMessageThreadIAmNotIn()
    {
        $thread = self::$testUser->privateMessageThread();

        // Maybe this should throw an exception rather than null??
        $this->assertNull($thread);
    }

    public function testGetPrivateMessageThreadsWithTustin()
    {
        // This thread should exist as long as I don't accidentally leave it ;)
        $thread = self::$tustinUser->privateMessageThread();

        $this->assertInstanceOf('\PlayStation\Api\MessageThread', $thread);

        return $thread;
    }

    /**
     * @depends testGetPrivateMessageThreadsWithTustin
     */
    public function testCheckPrivateMessageMemberCount(MessageThread $thread)
    {
        $this->assertEquals($thread->memberCount(), 2);
    }

    /**
     * @depends testGetPrivateMessageThreadsWithTustin
     */
    public function testCheckPrivateMessageName(MessageThread $thread)
    {
        $this->assertEquals($thread->name(), 'psn-php');
    }

    /**
     * @depends testGetPrivateMessageThreadsWithTustin
     */
    public function testSendMessageToPrivateMessageThread(MessageThread $thread)
    {
        $message = $thread->sendMessage('Hello @ ' . time());

        $this->assertInstanceOf('\PlayStation\Api\Message', $message);

        return $message;
    }

    /**
     * @depends testSendMessageToPrivateMessageThread
     */
    public function testWasMessageSentByTheLoggedInUser(Message $message)
    {
        $sender = $message->sender();

        $this->assertInstanceOf('\PlayStation\Api\User', $sender);

        $this->assertEquals($sender->onlineId(), self::$loggedInUser->onlineId());
    }
}