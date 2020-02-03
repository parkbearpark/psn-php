<?php
namespace Tustin\PlayStation\Api\Message;

use wapmorgan\Mp3Info\Mp3Info;
use Tustin\PlayStation\Api\Enum\MessageType;
use Tustin\PlayStation\Api\Message\AbstractMessage;

class AudioMessage extends AbstractMessage
{
    private $audioFile;

    public function __construct(Mp3Info $audioFile)
    {
        $this->audioFile = $audioFile;

        parent::__construct(MessageType::audio());
    }

    /**
     * Creates a new AudioMessage from a file path on the system.
     *
     * @param string $filePath
     * @return AudioMessage
     */
    public static function fromFilePath(string $filePath) : AudioMessage
    {
        if (!file_exists($filePath))
        {
            throw new \InvalidArgumentException('$filePath does not exist.');
        }
        
        return static::fromMp3(
            new Mp3Info($filePath)
        );
    }

    /**
     * Creates a new AudioMessage from an existing Mp3Info instance.
     *
     * @param Mp3Info $file
     * @return AudioMessage
     */
    public static function fromMp3(Mp3Info $info) : AudioMessage
    {
        return new static($info);
    }

    public function build() : array
    {
        return $this->scaffold(
        [
            'body' => '',
            'voiceDetail' => [
                'playbackTime' => $this->audioFile->duration % 60
            ]
        ],
        [
            'name' => 'voiceData',
            'contents' => fopen($this->audioFile->_fileName, 'r'),
            'headers' => [
                'Content-Type' => 'audio/3gpp',
                'Content-Transfer-Encoding' => 'binary',
            ]
        ]);
    }
}