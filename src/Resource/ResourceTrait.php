<?php

namespace Tustin\PlayStation\Resource;

trait ResourceTrait
{
    private $path;
    
    public function __construct(string $resourcePath)
    {
        $this->path = $resourcePath;
    }

    public function path() : string
    {
        return $this->path;
    }

    public function data() : string
    {
        return file_get_contents($this->path);
    }
}