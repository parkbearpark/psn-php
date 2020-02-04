<?php
namespace Tustin\PlayStation\Api\Model;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Enum\LanguageType;

class TrophyGroup extends Model
{
    // private string $npCommunicationId;
    // private LanguageType $language;

    // public function __construct(Client $client, string $npCommunicationId, LanguageType $language)
    // {
    //     if (!LanguageType::isValid($language))
    //     {
    //         throw new \InvalidArgumentException('$language is not a valid member of ' . LanguageType::class);
    //     }
        
    //     parent::__construct($client);

    //     $this->npCommunicationId = $npCommunicationId;
    //     $this->language = $language;
    // }

    // public function inf() : object
    // {
    //     return $this->cache ??= $this->get('')
    // }

}