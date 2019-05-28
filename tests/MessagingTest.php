<?php

namespace PlayStation\Tests;

use PlayStation\Api\MessageThread;
use PlayStation\Api\Message;

class MessagingTest extends PlayStationApiTestCase
{
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
    public function testGetAllMessagesInMessageThread(MessageThread $thread)
    {
        $messages = $thread->messages(3);

        $this->assertInternalType('array', $messages);

        $this->assertEquals(count($messages), 3);

        $message = $messages[0];

        $this->assertInstanceOf('\PlayStation\Api\Message', $message);
    }

    /**
     * @depends testGetPrivateMessageThreadsWithTustin
     */
    public function testSetMessageThreadThumbnailImage(MessageThread $thread)
    {
        $pugImagePath = realpath(__DIR__ . '/files/pug.jpg');
        $pugImage = file_get_contents($pugImagePath);
        $this->assertTrue($thread->setThumbnail($pugImage));
    }

    /**
     * @depends testGetPrivateMessageThreadsWithTustin
     */
    public function testFavoriteMessageThread(MessageThread $thread)
    {
        $this->assertTrue($thread->favorite());
    }

    /**
     * @depends testGetPrivateMessageThreadsWithTustin
     */
    public function testUnFavoriteMessageThread(MessageThread $thread)
    {
        $this->assertTrue($thread->unfavorite());
    }

    /**
     * @depends testGetPrivateMessageThreadsWithTustin
     */
    public function testRemoveMessageThreadThumbnailImage(MessageThread $thread)
    {
        $this->assertTrue($thread->removeThumbnail());
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

    /**
     * @depends testSendMessageToPrivateMessageThread
     */
    public function testDoesMessageContainTheActualContents(Message $message)
    {
        $this->assertContains('Hello', $message->body());
    }
}