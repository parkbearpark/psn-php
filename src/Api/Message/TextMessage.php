<?php
namespace Tustin\PlayStation\Api\Message;

use Tustin\PlayStation\Enum\MessageType;
use Tustin\PlayStation\Api\Message\AbstractMessage;

class TextMessage extends AbstractMessage
{
    private string $body;

    public function __construct(string $body)
    {
        $this->body = $body;

        parent::__construct(MessageType::text());
    }

    public function build() : array
    {
        return $this->scaffold([
            'body' => $this->body
        ]);
    }
}