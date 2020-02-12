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

    /**
     * Filters titles that only have trophy groups.
     *
     * @return Iterator
     */
    public function withTrophyGroups() : Iterator
    {
        return $this->where(function($trophy) {
            return $trophy->hasTrophyGroups();
        });
    }

    /**
     * Filter title by the exact title name.
     *
     * @param string $name
     * @return Iterator
     */
    public function withName(string $name) : Iterator
    {
        return $this->where(function($trophy) use ($name) {
            return $trophy->name() === $name;
        });
    }

    /**
     * Filter title by the exact NP communication ID. (NPWR_xxx)
     *
     * @param string $id
     * @return TrophyTitle
     */
    public function withId(string $id) : TrophyTitle
    {
        $this->where(function($trophy) use ($id) {
            return $trophy->npCommuncationId() === $id;
        });
        
        return $this->first();
    }

    /**
     * Only gives trophy titles with a trophy count less than the criteria passed.
     *
     * @param integer $criteria
     * @return Iterator
     */
    public function withTrophyCountLessThan(int $criteria) : Iterator
    {
        return $this->where(function($trophy) use ($criteria) {
            return $trophy->trophyCount() < $criteria;
        });
    }

    /**
     * Only gives trophy titles with a trophy count less than or equal to the criteria passed.
     *
     * @param integer $criteria
     * @return Iterator
     */
    public function withTrophyCountLessThanEqual(int $criteria) : Iterator
    {
        return $this->where(function($trophy) use ($criteria) {
            return $trophy->trophyCount() <= $criteria;
        });
    }

    /**
     * Only gives trophy titles with a trophy count greater than the criteria passed.
     *
     * @param integer $criteria
     * @return Iterator
     */
    public function withTrophyCountGreaterThan(int $criteria) : Iterator
    {
         return $this->where(function($trophy) use ($criteria) {
            return $trophy->trophyCount() > $criteria;
        });
    }

    /**
     * Only gives trophy titles with a trophy count greater than or equal to the criteria passed.
     *
     * @param integer $criteria
     * @return Iterator
     */
    public function withTrophyCountGreaterThanEqual(int $criteria) : Iterator
    {
        return $this->where(function($trophy) use ($criteria) {
            return $trophy->trophyCount() >= $criteria;
        });
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