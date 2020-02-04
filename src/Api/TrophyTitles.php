<?php
namespace Tustin\PlayStation\Api;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Enum\ConsoleType;
use Tustin\PlayStation\Api\Enum\LanguageType;
use Tustin\PlayStation\Api\Model\TrophyTitle;
use Tustin\PlayStation\Exception\NotFoundException;
use Tustin\PlayStation\Iterator\TrophyTitlesIterator;

class TrophyTitles extends Api
{
    private LanguageType $language;
    
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
     * Gets all the trophy titles.
     *
     * @param integer $limit
     * @return TrophyTitlesIterator
     */
    public function all(int $limit = 128) : TrophyTitlesIterator
    {
        return new TrophyTitlesIterator(
            $this->httpClient, 
            $this->language, 
            [ ConsoleType::ps4(), ConsoleType::ps3(), ConsoleType::vita() ],
            $limit
        );
    }

    /**
     * Gets trophy titles for only the specific console(s).
     *
     * @param integer $limit
     * @param ConsoleType ...$consoles
     * @return TrophyTitlesIterator
     */
    public function forConsole(array $consoles, int $limit = 128) : TrophyTitlesIterator
    {
        return new TrophyTitlesIterator(
            $this->httpClient, 
            $this->language, 
            $consoles,
            $limit
        );
    }
    
    /**
     * Finds a trophy title by the game name.
     *
     * @param string $name
     * @return TrophyTitle
     */
    public function findByName(string $name) : TrophyTitle
    {
        foreach ($this->all() as $title)
        {
            if ($title->name() === $name)
            {
                return $title;
            }
        }

        throw new NotFoundException("No such trophy titiel with name $name found.");
    }

    /**
     * Gets the latest trophy title.
     *
     * @return TrophyTitle
     */
    public function latest() : TrophyTitle
    {
        // Shouldn't need to rewind the iterator since it's creating a new iterator instance.
        // Might need to change this in the future if we stick with a static iterator.
        return $this->all()->current();
    }
}