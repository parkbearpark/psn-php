<?php
namespace Tustin\PlayStation\Iterator;

use RuntimeException;
use Tustin\PlayStation\Api\Model\Story;
use Tustin\PlayStation\Api\FeedRepository;

class FeedIterator extends AbstractApiIterator
{
    /**
     * Feed Repository.
     *
     * @var FeedRepository
     */
    private $feedRepository;
    
    public function __construct(FeedRepository $feedRepository)
    {
        parent::__construct($feedRepository->httpClient);

        $this->limit = 10;
        $this->access(0);
    }

    public function access($cursor) : void
    {
        $user = $this->feedRepository->getUser();

        // I don't think the API actually cares what page is set. It seems to just use the offset either way.
        $results = $this->get('https://activity.api.np.km.playstation.net/activity/api/v2/users/' . $user->onlineId() . '/feed/1', [
            'includeComments' => $this->feedRepository->getIncludeComments(),
            'offset' => $cursor,
            'blockSize' => $this->limit
        ]);

        $this->lastBlock = $results->lastBlock;

        // Because why would you include the total amount of items Sony???
        $this->update(-1, $results->feed);
    }

    /**
     * Do not use. Total results is not possible in the FeedIterator.
     * 
     * Will always throw RuntimeException.
     *
     * @return integer
     * @throws RuntimeException
     */
    public function getTotalResults() : int
    {
        throw new RuntimeException("getTotalResults is not supported by the feed API.");
    }

    /**
     * Checks if the current offset exists.
     * 
     * $offset is negated in the FeedIterator because Sony doesn't use an offset.
     * We instead rely on a bool propetty $lastBlock.
     *
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset) : bool
    {
        return !$this->lastBlock;
    }

    /**
     * Gets the current story.
     *
     * @return Story
     */
    public function current() : Story
    {
        return Story::fromObject(
            $this->feedRepository,
            $this->getFromOffset($this->currentOffset),
        );
    }
}