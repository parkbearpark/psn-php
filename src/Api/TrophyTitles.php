<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use GuzzleHttp\Client;
use IteratorAggregate;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Enum\ConsoleType;
use Tustin\PlayStation\Enum\LanguageType;
use Tustin\PlayStation\Api\Model\TrophyTitle;
use Tustin\PlayStation\Iterator\TrophyTitlesIterator;
use Tustin\PlayStation\Iterator\Filter\TrophyTitle\TrophyTitleNameFilter;
use Tustin\PlayStation\Iterator\Filter\TrophyTitle\TrophyTitleHasGroupsFilter;

class TrophyTitles extends Api implements IteratorAggregate
{
    private LanguageType $language;

    private array $platforms = [];

    private string $withName = '';

    private ?bool $hasTrophyGroups = null;

    public function __construct(Client $client, LanguageType $language = null)
    {
        parent::__construct($client);

        // @Hack: Doing this until PHP lets us use class constants as default param values...
        if ($language == null)
        {
            $language = LanguageType::english();
        }
        
        $this->language = $language;
    }

    /**
     * Filters trophy titles only for the supplied platform(s).
     *
     * @param ConsoleType ...$platforms
     * @return void
     */
    public function forPlatform(ConsoleType ...$platforms)
    {
        $this->platforms = $platforms;

        return $this;
    }

    /**
     * Filters trophy titles that either have trophy groups or no trophy groups.
     *
     * @param boolean $value
     * @return boolean
     */
    public function hasTrophyGroups(bool $value = true)
    {
        $this->hasTrophyGroups = $value;

        return $this;
    }

    /**
     * Filters trophy titles to only get titles containing the supplied name.
     *
     * @param string $name
     * @return void
     */
    public function withName(string $name)
    {
        $this->withName = $name;
        
        return $this;
    }

    /**
     * Gets the iterator and applies any filters.
     *
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        $iterator = new TrophyTitlesIterator($this->httpClient, $this->language, $this->platforms);

        if ($this->withName)
        {
            $iterator = new TrophyTitleNameFilter($iterator, $this->withName);
        }

        if (!is_null($this->hasTrophyGroups))
        {
            $iterator = new TrophyTitleHasGroupsFilter($iterator, $this->hasTrophyGroups);
        }

        return $iterator;
    }

    /**
     * Gets the first trophy title in the collection.
     *
     * @return TrophyTitle
     */
    public function first() : TrophyTitle
    {
        return $this->getIterator()->current();
    }
}