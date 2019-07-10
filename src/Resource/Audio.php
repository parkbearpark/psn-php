<?php

namespace Tustin\PlayStation\Resource;

class Audio
{
    use ResourceTrait;

    // @NeedsTesting
    public function type() : string
    {
        $h = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($h, $this->path);
        finfo_close($h);

        return $type;
    }
}