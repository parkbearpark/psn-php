<?php

namespace Tustin\PlayStation\Api\Model;

use Tustin\PlayStation\Api\Api;

abstract class Model extends Api
{
    protected ?object $cache = null;

    public function setCache(object $data) : self
    {
        $this->cache = $data;

        return $this;
    }
}