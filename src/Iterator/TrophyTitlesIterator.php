<?php
namespace Tustin\PlayStation\Iterator;

use Tustin\PlayStation\Api\Model\TrophyTitle;
use Tustin\PlayStation\Api\TrophyTitlesRepository;

class TrophyTitlesIterator extends AbstractApiIterator
{
    private $platforms;

    private $trophyTitles;
    
    public function __construct(TrophyTitlesRepository $titles)
    {
        parent::__construct($titles->httpClient);

        $this->trophyTitles = $titles;
        
        $this->platforms = implode(',', $titles->getPlatforms());

        $this->limit = 128;
        
        $this->access(0);
    }

    public function access($cursor) : void
    {
        $body = [
            'fields' => implode(',', [
                '@default'
            ]),
            'platform' => $this->platforms,
            'limit' => $this->limit,
            'offset' => $cursor,
            'npLanguage' => $this->trophyTitles->getLanguage()->getValue()
        ];

        if (!is_null($this->trophyTitles->getComparedUser()))
        {
            $body['comparedUser'] = $this->trophyTitles->getComparedUser()->onlineId();
        }

        $results = $this->get('https://us-tpy.np.community.playstation.net/trophy/v1/trophyTitles', $body);

        $this->update($results->totalResults, $results->trophyTitles);
    }

    public function current()
    {
        return TrophyTitle::fromObject(
            $this->trophyTitles,
            $this->getFromOffset($this->currentOffset)
        );
    }
}