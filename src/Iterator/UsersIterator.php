<?php
namespace Tustin\PlayStation\Iterator;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;

class UsersIterator extends Api implements \Iterator
{
    protected int $currentOffset = 0;

    protected int $currentIndexer = 0;

    protected int $limit;

    protected string $query;

    protected int $totalResults;

    protected array $cache = [];

    public function __construct(Client $client, string $query, int $limit = 50)
    {
        parent::__construct($client);
        $this->query = $query;
        $this->limit = $limit;
        $this->access(0);
    }

    public function access(int $offset)
    {
        $results = $this->get('https://friendfinder.api.np.km.playstation.net/friend-finder/api/v1/users/me/search', [
            'fields' => 'onlineId',
            'query' => $this->query,
            'searchTarget' => 'all',
            'searchFields' => 'onlineId',
            'limit' => $this->limit,
            'offset' => $offset,
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

    public function rewind()
    {
        $this->currentOffset = 0;
        $this->currentIndexer = 0;
    }

    public function valid()
    {
        return array_key_exists($this->currentIndexer, $this->cache);
    }

    public function next()
    {
        $this->currentOffset++;
        $this->currentIndexer++;
        if (($this->currentOffset % $this->limit) == 0)
        {
            // Needs to account for totalResults as well.
            $this->access($this->currentOffset);
            $this->currentIndexer = 0;
        }
    }

    public function key() : int
    {
        return $this->currentOffset;
    }

    public function getTotalResults() : int
    {
        return $this->totalResults;
    }

    private function setTotalResults(int $results) : void
    {
        $this->totalResults = $results;
    }
}