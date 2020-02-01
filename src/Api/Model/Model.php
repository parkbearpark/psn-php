<?php

namespace Tustin\PlayStation\Api\Model;

use Tustin\PlayStation\Api\Api;

abstract class Model extends Api
{
    private ?object $cache = null;
}