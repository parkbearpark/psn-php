<?php
namespace Tustin\PlayStation\Api\Model;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Enum\LanguageType;

class TrophyTitle extends Model
{
    private string $npCommunicationId;
    private LanguageType $language;

    public function __construct(Client $client, string $npCommunicationId, LanguageType $language)
    {
        parent::__construct($client);

        $this->npCommunicationId = $npCommunicationId;
        $this->language = $language;
    }

    /**
     * Gets the NP communication ID (NPWR_) for this trophy title.
     *
     * @return string
     */
    public function npCommunicationId() : string
    {
        return $this->npCommunicationId;
    }

    public function info() : object
    {
        return $this->cache ??= $this->get(
            'https://us-tpy.np.community.playstation.net/trophy/v1/trophyTitles/' . $this->npCommunicationId() . '/trophyGroups',
            [
                'fields' => implode(',', [
                    '@default',
                    'trophyTitleSmallIconUrl',
                    'trophyGroupSmallIconUrl'
                ]),
                'iconSize' => 'm',
                //comparedUser,
                'npLanguage' => $this->language->getValue()
            ]);
    }
}