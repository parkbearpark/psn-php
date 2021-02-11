<?php
namespace Tustin\PlayStation\Iterator;

use InvalidArgumentException;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Api\UsersRepository;

class UsersSearchIterator extends AbstractApiIterator
{
    /**
     * The search query.
     *
     * @var string
     */
    protected $query;

    /**
     * The fields to search in (comma delimited).
     *
     * @var string
     */
    protected $searchFields;

    /**
     * The users repository.
     *
     * @var UsersRepository
     */
    private $usersRepository;
    
    public function __construct(UsersRepository $usersRepository, string $query, array $searchFields)
    {
        if (empty($query))
        {
            throw new InvalidArgumentException('[query] must contain a value.');
        }

        if (empty($searchFields))
        {
            throw new InvalidArgumentException('[searchFields] must contain at least one value.');
        }

        parent::__construct($usersRepository->httpClient);
        $this->usersRepository = $usersRepository;
        $this->query = $query;
        $this->limit = 50;
        $this->searchFields = implode(',', $searchFields);
        $this->access(0);
    }

    public function access($cursor) : void
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

        $this->update($results->totalResults, $results->searchResults);
    }

    public function current()
    {
        return User::fromObject(
            $this->usersRepository,
            $this->getFromOffset($this->currentOffset)
        );
    }
}