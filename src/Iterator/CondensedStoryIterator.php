<?php
namespace Tustin\PlayStation\Iterator;

use Tustin\PlayStation\Api\Model\Story;
use Tustin\PlayStation\Api\FeedRepository;

class CondensedStoryIterator extends AbstractInternalIterator
{
    /**
     * The feed repository.
     *
     * @var FeedRepository
     */
    private $feedRepository;
    
    public function __construct(FeedRepository $feedRepository, array $stories = [])
    {
        $this->create(function ($story) use ($feedRepository) {
            return Story::fromObject($feedRepository, (object)$story);
        }, $stories);
    }
}