<?php
namespace Tustin\PlayStation\Iterator;

use Iterator;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Tustin\PlayStation\Enum\ConsoleType;
use Tustin\PlayStation\Enum\LanguageType;
use Tustin\PlayStation\Api\Model\TrophyTitle;

class TrophyTitlesIterator extends AbstractApiIterator
{
    private string $platforms;
    private LanguageType $language;

    public function __construct(Client $client, LanguageType $language, array $platforms, int $limit = 128)
    {
        if (empty($platforms) || is_null($platforms))
        {
            throw new InvalidArgumentException('$platforms needs at least one ' . ConsoleType::class);
        }

        // @TODO: Add check for each $platforms to ensure validity.

        if ($limit <= 0)
        {
            throw new InvalidArgumentException('$limit must be greater than zero.');
        }

        parent::__construct($client);
        $this->limit = $limit;
        $this->language = $language;
        $this->platforms = implode(',', $platforms);
        $this->access(0);
    }

    public function access($cursor) : void
    {
        $results = $this->get('https://us-tpy.np.community.playstation.net/trophy/v1/trophyTitles', [
            'fields' => implode(',' ,[
                '@default'
            ]),
            'platform' => $this->platforms,
            'limit' => $this->limit,
            'offset' => $cursor,
            //comparedUser
            'npLanguage' => $this->language->getValue()
        ]);

        $this->update($results->totalResults, $results->trophyTitles);
    }

    public function current()
    {
        return new TrophyTitle(
            $this->httpClient,
            $this->getFromOffset($this->currentOffset),
            $this->language
        );
    }
}