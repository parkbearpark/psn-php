<?php
namespace Tustin\PlayStation\Iterator;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;

class FriendsIterator extends ApiIterator
{
    protected string $parameter;
    
    protected string $sort;
    
    public function __construct(Client $client, string $parameter, string $sort, int $limit)
    {
        if (empty($parameter))
        {
            throw new \InvalidArgumentException('$parameter must not be empty.');
        }

        if (empty($sort))
        {
            throw new \InvalidArgumentException('$sort must not be empty.');
        }

        if ($limit <= 0)
        {
            throw new \InvalidArgumentException('$limit must be greater than zero.');
        }

        parent::__construct($client);
        $this->parameter = $parameter;
        $this->sort = $sort;
        $this->limit = $limit;
        $this->access(0);
    }

    public function access($cursor)
    {
        $results = $this->get('https://us-prof.np.community.playstation.net/userProfile/v1/users/' . $this->parameter . '/friends/profiles2', [
            'fields' => 'onlineId',
            'limit' => $this->limit,
            'offset' => $cursor,
            'sort' => $this->sort,
        ]);

        // Just set this each time for brevity.
        $this->setTotalResults($results->totalResults);

        $this->cache = $results->profiles;
    }

    public function current()
    {
        return new User($this->httpClient, $this->cache[$this->currentIndexer]->onlineId);
    }
}