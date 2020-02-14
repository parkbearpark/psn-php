<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use GuzzleHttp\Client;
use IteratorAggregate;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Enum\ConsoleType;
use Tustin\PlayStation\Enum\LanguageType;
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

    public function platform(ConsoleType ...$platforms)
    {
        $this->platforms = $platforms;

        return $this;
    }

    public function hasTrophyGroups(bool $value = true)
    {
        $this->hasTrophyGroups = $value;

        return $this;
    }

    public function withName(string $name)
    {
        $this->withName = $name;
        
        return $this;
    }

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
}