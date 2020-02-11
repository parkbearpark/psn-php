<?php
namespace Tustin\PlayStation\Traits;

use ReflectionClass;
use RuntimeException;
use InvalidArgumentException;
use Tustin\PlayStation\Contract\Fetchable;

trait Model
{
    private array $cache = [];

    /**
     * Plucks an API property from the cache. Will populare cache if necessary
     *
     * @param string $property
     * @param bool $ignoreCache
     * @return mixed
     */
    public function pluck(string $property, bool $ignoreCache = false)
    {
        if (!$this->hasCached() || $ignoreCache)
        {
            if (!(new ReflectionClass($this))->implementsInterface(Fetchable::class))
            {
                throw new RuntimeException('Model [' . get_class($this) . '] has not been cached, 
                but doesn\'t implement Fetchable to make requests.');
            }
            
            $this->setCache($this->fetch());
            $this->pluck($property);
        }
        
        if (empty($this->cache))
        {
            throw new InvalidArgumentException('Failed to populate cache for model [' . get_class($this) . ']');
        }

        $pieces = explode('.', $property);

        $root = $pieces[0];

        if (!array_key_exists($root, $this->cache))
        {
            throw new InvalidArgumentException("[$root] is not a valid property for model [" . get_class($this) . "]");
        }

        $value = $this->cache[$root];

        array_shift($pieces);

        foreach ($pieces as $piece)
        {
            if (!is_array($value))
            {
                throw new RuntimeException("Value [$value] passed to pluck is not an array, but tried accessing a key from it.");
            }
            
            $value = $value[$piece];
        }

        return $value;

    }

    protected function hasCached() : bool
    {
        return isset($this->cache) && !empty($this->cache);
    }

    protected function setCache($data)
    {
        foreach ((array)$data as $key => $item)
        {
            if (is_object($item))
            {
                $item = (array)$item;
            }
            
            $this->cache[$key] = $item;
        }
    }
}