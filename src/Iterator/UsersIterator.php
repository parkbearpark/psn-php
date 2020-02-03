<?php
namespace Tustin\PlayStation\Iterator;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;

class UsersIterator extends Api implements \Iterator
{
    use ApiIterator;

    protected string $query;

    protected string $searchFields;
    
    public function __construct(Client $client, string $query, array $searchFields, int $limit)
    {
        if (empty($query))
        {
            throw new \InvalidArgumentException('$query must contain a value.');
        }

        if (empty($searchFields))
        {
            throw new \InvalidArgumentException('$searchFields must contain at least one value.');
        }

        if ($limit <= 0)
        {
            throw new \InvalidArgumentException('$limit must be greater than zero.');
        }

        parent::__construct($client);
        $this->query = $query;
        $this->limit = $limit;
        $this->searchFields = implode(',', $searchFields);
        $this->access(0);
    }

    public function access($cursor)
    {
        $results = $this->get('https://friendfinder.api.np.km.playstation.net/friend-finder/api/v1/users/me/search', [
            'fields' => 'onlineId',
            'query' => $this->query,
            'searchTarget' => 'all',
            'searchFields' => $this->searchFields,
            'limit' => $this->limit,
            'offset' => $cursor,
            'rounded' => true
        ]);

        // Just set this each time for brevity.
        $this->setTotalResults($results->totalResults);

        $this->cache = $results->searchResults;
    }

    public function current()
    {
        return new User($this->httpClient, $this->cache[$this->currentIndexer]->onlineId);
    }
}