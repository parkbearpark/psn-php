<?php
namespace Tustin\PlayStation\Api\Model;

class StoryComponent
{
    /**
     * The component key
     *
     * @var string
     */
    private $key;

    /**
     * The component value.
     *
     * @var string
     */
    private $value;

    public function __construct(string $key, string $value)
    {
        $this->key = $key;
        $this->value = $value;       
    }

    /**
     * Initializes a new story component from existing component data.
     *
     * @param array $component
     * @return StoryComponent
     */
    public static function fromArray(array $component) : StoryComponent
    {
        return new static($component['key'], $component['value']);
    }

    /**
     * Gets the component key.
     *
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * Gets the key prefixed by a dollar sign.
     * 
     * This is how it will be defined in the template string.
     *
     * @return string
     */
    public function getKeyInTemplate() : string
    {
        return '$' . $this->key;
    }

    /**
     * Gets the component value.
     *
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }
}