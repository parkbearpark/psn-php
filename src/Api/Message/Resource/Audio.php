<?php
namespace Tustin\PlayStation\Api\Message\Resource;

class Audio
{
    use ResourceTrait;

    public function type() : string
    {
        $h = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($h, $this->path);
        finfo_close($h);

        return $type;
    }
}