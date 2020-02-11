<?php
namespace Tustin\PlayStation\Api;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Enum\ConsoleType;
use Tustin\PlayStation\Enum\LanguageType;
use Tustin\PlayStation\Api\Model\TrophyTitle;
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
     * Creates a new title iterator for the supplied consoles.
     *
     * @param integer $limit
     * @param ConsoleType ...$consoles
     * @return void
     */
    private function create(int $limit, ConsoleType ...$consoles) : TrophyTitlesIterator
    {
        return new TrophyTitlesIterator(
            $this->httpClient, 
            $this->language, 
            $consoles,
            $limit
        );
    }

    /**
     * Gets all the trophy titles.
     *
     * @param integer $limit
     * @return TrophyTitlesIterator
     */
    public function all(int $limit = 128) : TrophyTitlesIterator
    {
        return $this->create($limit,  ConsoleType::ps4(), ConsoleType::ps3(), ConsoleType::vita());
    }

    /**
     * Gets trophy titles for only the specific console(s).
     *
     * @param integer $limit
     * @param ConsoleType $consoles
     * @return TrophyTitlesIterator
     */
    public function forConsole(int $limit = 128, ConsoleType ...$consoles) : TrophyTitlesIterator
    {
        return $this->create($limit, ...$consoles);
    }

    public function findById(string $id, int $limit = 128) : TrophyTitle
    {
        return $this->all($limit)
        ->withId($id)
        ->first();
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