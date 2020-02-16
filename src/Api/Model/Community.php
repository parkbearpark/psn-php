<?php

namespace Tustin\PlayStation\Api\Model;

use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Interfaces\Fetchable;
use Tustin\PlayStation\Api\CommunitiesRepository;

class Community extends Api implements Fetchable
{
    use Model;
    
    private $communitiesRepository;

    private $communityId;

    public function __construct(CommunitiesRepository $communitiesRepository, string $communityId)
    {
        parent::__construct($communitiesRepository->getHttpClient());

        $this->communitiesRepository = $communitiesRepository;
        $this->communityId = $communityId;
    }

    public static function fromObject(CommunitiesRepository $communitiesRepository, object $data)
    {
        $instance = new static($communitiesRepository, $data->id);
        $instance->setCache($data);
        return $instance;
    }

    /**
     * Gets the message thread ID.
     *
     * @return string
     */
    public function id() : string
    {
        return $this->communityId;
    }

    /**
     * Gets the community info from the PlayStation API.
     *
     * @return object
     */
    public function fetch() : object
    {
        return $this->get('https://communities.api.playstation.com/v1/communities/' . $this->id(), [
            'includeFields' => implode(',', [
                'members',
                'size',
            ])
        ]);
    }
}