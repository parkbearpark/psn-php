<?php

namespace Tustin\PlayStation\Resource;

class Image extends ResourceTrait
{
    public function type() : int
    {
        return exif_imagetype($this->path);
    }
}