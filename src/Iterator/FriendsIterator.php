<?php
namespace Tustin\PlayStation\Iterator;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;

class FriendsIterator extends Api implements \Iterator
{
    use ApiIterator;

    protected string $parameter;
    
    protected string $sort;
    
    public function __construct(Client $client, string $parameter, string $sort, int $limit = 50)
    {
        parent::__construct($client);
        $this->parameter = $parameter;
        $this->sort = $sort;
        $this->limit = $limit;
        $this->access(0);
    }

    /**
     * Accesses a new 'page' of search results.
     *
     * @param integer $offset
     * @return void
     */
    public function access(int $offset)
    {
        $results = $this->get('https://us-prof.np.community.playstation.net/userProfile/v1/users/' . $this->parameter . '/friends/profiles2', [
            'fields' => 'onlineId',
            'limit' => $this->limit,
            'offset' => $offset,
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