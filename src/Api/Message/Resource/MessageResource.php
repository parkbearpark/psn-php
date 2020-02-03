<?php

namespace Tustin\PlayStation\Resource;

abstract class MessageResource
{
    private $info;
    
    public function __construct(string $info)
    {
        $this->info = $info;
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