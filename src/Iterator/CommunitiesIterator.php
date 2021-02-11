<?php
namespace Tustin\PlayStation\Iterator;

use RuntimeException;
use Tustin\PlayStation\Api\Model\Community;
use Tustin\PlayStation\Api\CommunitiesRepository;

class CommunitiesIterator extends AbstractApiIterator
{
    /**
     * The users repository.
     *
     * @var CommunitiesRepository
     */
    private $communitiesRepository;

    public function __construct(CommunitiesRepository $communitiesRepository)
    {
        parent::__construct($communitiesRepository->getHttpClient());

        $this->communitiesRepository = $communitiesRepository;
        $this->limit = 10;
        $this->access(0);
    }

    public function access($cursor) : void
    {
        if (!is_null($this->communitiesRepository->getUser()))
        {
            $results = $this->retrieve($cursor);
        }
        else if (!is_null($this->communitiesRepository->getQuery()))
        {
            $results = $this->search($cursor);
        }
        else
        {
            throw new RuntimeException('[' . CommunitiesIterator::class . '] requires an instance of ['. CommunitiesRepository::class .'] that has 
            called either CommunitiesIterator::setUser or CommunitiesIterator::setQuery');
        }

        $this->update($results->total, $results->communities);
    }

    private function retrieve($cursor) : object
    {
        $body = [
            'fields' => implode(',', [
                'backgroundImage',
                'description',
                'id',
                'isCommon',
                'members',
                'name',
                'profileImage',
                'role',
                'unreadMessageCount',
                'sessions',
                'timezoneUtcOffset',
                'language',
                'titleName'
            ]),
            'includeFields' => implode(',', $this->communitiesRepository->getIncludeFields()),
            'limit' => $this->limit,
            'offset' => $cursor,
            'sort' => 'common',
            'onlineId' => $this->communitiesRepository->getUser()->onlineId(),
        ];

        $language = $this->communitiesRepository->getLanguage();

        if (!is_null($language))
        {
            $body['npLanguage'] = $language->getValue();
        }

        return $this->get('https://communities.api.playstation.com/v1/communities', $body);
    }

    private function search($cursor) : object
    {
        $body = [
            'query' => $this->communitiesRepository->getQuery(),
            'includeFields' => implode(',', $this->communitiesRepository->getIncludeFields()),
            'limit' => $this->limit,
            'offset' => $cursor,
            'rounded' => true
        ];

        $language = $this->communitiesRepository->getLanguage();

        if (!is_null($language))
        {
            $body['npLanguage'] = $language->getValue();
        }

        return $this->get('https://communities.api.playstation.com/v1/search', $body);
    }

    public function current()
    {
        return Community::fromObject(
            $this->communitiesRepository,
            $this->getFromOffset($this->currentOffset)
        );
    }
}