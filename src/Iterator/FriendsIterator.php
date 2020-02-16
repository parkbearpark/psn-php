<?php
namespace Tustin\PlayStation\Iterator;

use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Api\FriendsRepository;

class FriendsIterator extends AbstractApiIterator
{
    /**
     * The friends repository.
     *
     * @var FriendsRepository
     */
    private $friendsRepository;

    public function __construct(FriendsRepository $friendsRepository)
    {
        parent::__construct($friendsRepository->getHttpClient());

        $this->limit = 36;

        $this->access(0);
    }

    public function access($cursor) : void
    {
        $results = $this->get('https://us-prof.np.community.playstation.net/userProfile/v1/users/' . $this->friendsRepository->getUser()->onlineId() . '/friends/profiles2', [
            'fields' => 'onlineId',
            'limit' => $this->limit,
            'offset' => $cursor,
            'sort' => $this->friendsRepository->getSortBy(),
        ]);

        $this->update($results->totalResults, $results->profiles);
    }

    public function current() : User
    {
        return User::create(
            $this->friendsRepository->getHttpClient(),
            $this->getFromOffset($this->currentOffset)->onlineId,
            true
        );
    }
}