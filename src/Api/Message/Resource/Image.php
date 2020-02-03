<?php
namespace Tustin\PlayStation\Api\Message\Resource;

class Image
{
    use ResourceTrait;

    public function type() : string
    {
        $finfo = new \finfo(FILEINFO_MIME);
        $mime = $finfo->file($this->path);
        return explode(';', $mime)[0];
    }
}