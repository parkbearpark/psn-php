<?php
namespace Tustin\PlayStation\Api\Message;

use Tustin\PlayStation\Enum\MessageType;
use Tustin\PlayStation\Api\Message\AbstractMessage;

class ImageMessage extends AbstractMessage
{
    private $imageResource;

    public function __construct($imageResource)
    {
        if (!is_resource($imageResource))
        {
            throw new \InvalidArgumentException('$imageResource must be a file resource.');
        }
        
        $this->imageResource = $imageResource;

        parent::__construct(MessageType::image());
    }

    /**
     * Creates a new ImageMessage from a file path on the system.
     *
     * @param string $filePath
     * @return ImageMessage
     */
    public static function fromFilePath(string $filePath) : ImageMessage
    {
        if (!file_exists($filePath))
        {
            throw new \InvalidArgumentException('$filePath does not exist.');
        }
        
        return static::fromFile(
            fopen($filePath, 'r')
        );
    }

    /**
     * Creates a new ImageMessage from a file resource.
     *
     * @param resource $fileResource
     * @return ImageMessage
     */
    public static function fromFile($fileResource) : ImageMessage
    {
        return new static($fileResource);
    }

    public function build() : array
    {
        return $this->scaffold(
        [
            'body' => ''
        ],
        [
            'name' => 'imageData',
            'contents' => $this->imageResource,
            'headers' => [
                'Content-Type' => 'image/png',
                'Content-Transfer-Encoding' => 'binary',
            ]
        ]);
    }
}