<?php
namespace Tustin\PlayStation\Iterator;

use Iterator;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Filter\UserFilter;

class FriendsIterator extends ApiIterator
{
    protected string $parameter;
    
    protected string $sort;
    
    public function __construct(Client $client, string $parameter, string $sort, int $limit)
    {
        if (empty($parameter))
        {
            throw new InvalidArgumentException('$parameter must not be empty.');
        }

        if (empty($sort))
        {
            throw new InvalidArgumentException('$sort must not be empty.');
        }

        if ($limit <= 0)
        {
            throw new InvalidArgumentException('$limit must be greater than zero.');
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

        $this->update($results->totalResults, $results->profiles);
    }

    /**
     * Gets friends whose onlineId contains the specified string.
     *
     * @param string $text
     * @return Iterator
     */
    public function containing(string $text) : Iterator
    {
        yield from new UserFilter($this, $text);
    }

    public function current()
    {
        return new User(
            $this->httpClient,
            $this->getFromOffset($this->currentOffset)->onlineId,
            true
        );
    }
}