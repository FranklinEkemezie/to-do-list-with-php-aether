<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Utils;

use FranklinEkemezie\PHPAether\Exceptions\NotFoundException;
use FranklinEkemezie\PHPAether\Exceptions\UndefinedException;

/**
 * Dictionary class
 * 
 * Represent a dictionary or hash table
 * 
 * @author Ekemezie Franklin <franklynpeter2006@gmail.com>
 */
class Dictionary implements \JsonSerializable
{

    private array $items;

    public function __construct(array $items=[])
    {
        if (empty($items)) return;

        $this->setItems($items);
    }

    /**
     * Set a value with a given name
     * @param string $name
     * @param mixed $value
     * @return \FranklinEkemezie\PHPAether\Utils\Dictionary
     */
    public function set(string $name, mixed $value): self
    {
        $this->items[$name] = $value;
        return $this;
    }

    /**
     * Set the value which is an array with the given name as
     * a `Collection` (if indexed) or `Dictionary` (if associative)
     * @param string $name
     * @param array $value
     * @return \FranklinEkemezie\PHPAether\Utils\Dictionary
     */
    public function setArray(string $name, array $value): self
    {
        return $this->set($name, 
            array_is_list($value) ? new Collection(...$value) : new Dictionary($value)
        );
    }

    public function setItems(array $items): self
    {
        // Check if the array keys are strings
        if (array_is_list($items))
            throw new \InvalidArgumentException("Cannot use a list for argument #1");
        
        foreach($items as $name => $item) {
            $this->set($name, $item);
        }

        return $this;
    }

    /**
     * Get the value of an item
     * @param string $name The name of the item to get
     * @param string $throwErrorNotFound Whether to throw an error if not found
     * @return mixed Returns the item with the specified name or NULL if not found
     * @throws NotFoundException Throws error if not found and `$throwErrorNotFound` parameter is set to `true`
     */
    public function get(string $name, bool $throwErrorNotFound=false): mixed
    {
        return $this->items[$name] ?? (
            ! $throwErrorNotFound ? null :
            throw new NotFoundException("Item with name: $name not found")
        );
    }

    /**
     * Check if the dictionary has an item with the specified name
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->items[$name]);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function toJSON(): string
    {
        return json_encode($this->items);
    }

    public function __get(string $name): mixed
    {
        if ($this->has($name)) {
            return $this->get($name);
        }

        throw new UndefinedException('Undefined property: ' . __CLASS__ . '::$' . $name);
    }

    public function __toString(): string
    {
        return $this->toJSON();
    }

    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
