<?php
namespace Tustin\PlayStation\Api\Model;

use Carbon\Carbon;
use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Enum\StoryType;
use Tustin\PlayStation\Api\FeedRepository;
use Tustin\PlayStation\Iterator\CondensedStoryIterator;

class Story
{
    use Model;

    /**
     * The story's feed.
     *
     * @var FeedRepository
     */
    private $feedRepository;
    
    public function __construct(FeedRepository $feedRepository, object $story)
    {
        $this->setCache($story);

        $this->feedRepository = $feedRepository;
    }

    public static function fromObject(FeedRepository $feedRepository, object $story)
    {
        $instance = new static($feedRepository, $story);

        return $instance;
    }

    /**
     * Gets the feed this story is in.
     *
     * @return FeedRepository
     */
    public function feed() : FeedRepository
    {
        return $this->feedRepository;
    }

    /**
     * Gets all the condensed stories for this story.
     * 
     * Basically, a story can be a story that just includes other stories. You will typically see these for multiple trophy unlocks
     * for the same game. Sony does this to help prevent spam in the activity feed.
     *
     * @return CondensedStoryIterator
     */
    public function condensedStories() : CondensedStoryIterator
    {
        return new CondensedStoryIterator($this->pluck('condensedStories'));
    }

    /**
     * Gets the caption components that can be used in the caption template.
     * 
     * @see Story::captionTemplate()    To format using these components.
     *
     * @return array
     */
    public function captionComponents() : array
    {
        // TODO: Make this a list of proper classes to make it easier to decipher what components this story has.
        return $this->pluck('captionComponents');
    }

    /**
     * Gets the caption template that can be formatted for proper caption messages.
     * 
     * @see Story::captionComponents()  For all variables reference in this template.
     *
     * @return string
     */
    public function captionTemplate() : string
    {
        return $this->pluck('captionTemplate');
    }

    /**
     * Checks whether this story can be commented on.
     *
     * @return boolean
     */
    public function commentable() : bool
    {
        return $this->pluck('commentable') !== null;
    }

    /**
     * Gets the amount of comments on this story.
     *
     * @return integer
     */
    public function commentCount() : int
    {
        return $this->pluck('commentCount');
    }

    /**
     * Gets the date when this story was published.
     *
     * @return Carbon
     */
    public function date() : Carbon
    {
        // @NeedsTesting
        return Carbon::parse($this->pluck('date'))->setTimezone('UTC');
    }

    /**
     * Gets the large image URL for this story, if one exists.
     *
     * @return string|null
     */
    public function imageUrl() : ?string
    {
        return $this->pluck('largeImageUrl');
    }

    /**
     * Checks whether or not the client has liked this story.
     *
     * @return boolean
     */
    public function hasLiked() : bool
    {
        return $this->pluck('liked');
    }

    /**
     * Gets the amount of likes for this story.
     *
     * @return integer
     */
    public function likeCount() : int
    {
        return $this->pluck('likeCount');
    }

    /**
     * Gets the type for this story.
     *
     * @return StoryType
     */
    public function type() : StoryType
    {
        return new StoryType($this->pluck('storyType'));
    }

    /**
     * Gets all the targets this story is for.
     * 
     * This can include publisher name, game details, trophy details, etc.
     *
     * @return array
     */
    public function targets() : array
    {
        return $this->pluck('targets');
    }

    /**
     * Gets the title ID this story is for, if this story is for a game.
     *
     * @return string|null
     */
    public function titleId() : ?string
    {
        // @TODO: This should probably return a Game model once I get that finished.
        // Also, can this ever be null? I'm not sure if there can be a story for something that isn't a game.
        return $this->pluck('titleId');
    }

    /**
     * Gets the ID for this story.
     *
     * @return string
     */
    public function id() : string
    {
        return $this->pluck('storyId');
    }
}