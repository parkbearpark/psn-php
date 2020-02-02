<?php

namespace Tustin\PlayStation\Api\Model;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Model\Model;
use Tustin\PlayStation\Iterator\FriendsIterator;
use Tustin\PlayStation\Iterator\MembersIterator;
use Tustin\PlayStation\Iterator\MessagesIterator;

class MessageThread extends Model
{
    private string $threadId;

    private array $members;

    public function __construct(Client $client, string $threadId, array $members = [])
    {
        parent::__construct($client);

        $this->threadId = $threadId;
        $this->members = $members;
    }

    public function members() : MembersIterator
    {
        return new MembersIterator(
            !empty($this->members) ? $this->members : $this->info()->threadMembers
        );
    }

    public function memberCount() : int
    {
        return count($this->members);
    }


//GET https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/0cbb427788d6429b19d6c86b391058fed4dc685f-200?count=200&eventCategoryCodes=3&fields=threadMembers%2CthreadNameDetail%2CthreadThumbnailDetail%2CthreadProperty%2ClatestTakedownEventDetail%2CnewArrivalEventDetail%2CthreadEvents HTTP/1.1

    public function messages(int $count = 20) : MessagesIterator
    {
        return new MessagesIterator($this->httpClient, $this->threadId(), $count);
    }

    public function threadId() : string
    {
        return $this->threadId;
    }

    public function info(int $count = 1) : object
    {
        // This endpoint seems to have some kind of cursor for iterating messages but it's weird and I'm not too sure how it works.
        // maxEventIndexCursor=1#403213668053157
        // However the app never uses it so it's hard to say.
        return $this->cache ??= $this->get('https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/' . $this->threadId(), [
            'fields' => 'threadMembers,threadNameDetail,threadThumbnailDetail,threadProperty,latestTakedownEventDetail,newArrivalEventDetail,threadEvents',
            'count' => $count,
        ]);
    }
}