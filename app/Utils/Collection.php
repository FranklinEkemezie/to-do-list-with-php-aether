<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Utils;

/**
 * Collection class
 * 
 * Represents an indexed collection or list of items of related types
 * 
 * 
 */
class Collection implements \JsonSerializable
{

    public const COLLECTION_ARRAY   = 'array';
    public const COLLECTION_BOOL    = 'bool';
    public const COLLECTION_FLOAT   = 'float';
    public const COLLECTION_INT     = 'int';
    public const COLLECTION_NULL    = 'null';
    public const COLLECTION_NUMERIC = 'numeric';
    public const COLLECTION_OBJECT  = 'object';
    public const COLLECTION_RESOURCE= 'resource';
    public const COLLECTION_SCALAR  = 'scalar';
    public const COLLECTION_CALLABLE= 'callable';
    public const COLLECTION_ITERABLE= 'iterable';

    private string $type;
    private array $items;


    public function __construct(mixed ...$items)
    {
        $defaultType = gettype($items[0]);

        foreach($items as $index => $item) {
            if (static::checkItemMatchType($item, $defaultType)) continue;

            $itemNo = $index + 1;
            $itemType = gettype($item);

            throw new \InvalidArgumentException("Item #$itemNo of type $itemType does not match default type: $defaultType");
        } 

        $this->type = $defaultType;
        $this->items = $items;
    }

    private static function checkItemMatchType(mixed $item, string $type): bool
    {
        $typeCheckFnName = "is_$type";
        return
            function_exists($typeCheckFnName) &&
            call_user_func($typeCheckFnName, $item)
        ;
    }

    /**
     * Set the type of items to be stored in the collection.
     * This changes the default type set during instantiation.
     * Therefore, the new type must be compatible with that of all the items in the list already.
     */
    public function setType(string $type): self
    {
        $currTypePropertyName = "COLLECTION_" . strtoupper($type);
        if (! property_exists($this, $currTypePropertyName))
            throw new \InvalidArgumentException("Invalid or unsupported type: $type");

        $type = strtolower($type);
        foreach($this->items as $item) {
            if (! static::checkItemMatchType($item, $type));
                throw new \InvalidArgumentException(
                    "The new type: $type is not compatible with all items"
                );
        }

        $this->type = $type;
        return $this;
    }

    public function get(int $index): mixed
    {
        return $this->items[$index] ?? null;
    }

    /**
     * Insert an item in a collection
     * @param mixed $item The item to insert
     * @param int $index The index where to insert the item
     * @return \FranklinEkemezie\PHPAether\Utils\Collection
     */
    public function insert(mixed $item, int $index): self
    {
        return $this->insertItems($index, $item);
    }

    /**
     * Insert items from the start index
     * @param int $start The index to insert the first item
     * @param mixed[] $items A list of items to insert
     * @return \FranklinEkemezie\PHPAether\Utils\Collection
     */
    public function insertItems(int $start, mixed ...$items): self
    {
        foreach($items as $item) {
            if (! static::checkItemMatchType($item, $this->type)) {
                $itemType = gettype($item);
                throw new \InvalidArgumentException(
                    "Cannot insert item of type: $itemType to a collection of type: {$this->type}"
                );
            }
        }

        array_splice($this->items, $start, 0, $items);
        return $this;
    }

    /**
     * Remove an item from the collection
     * @param int $index The index of the item
     * @return \FranklinEkemezie\PHPAether\Utils\Collection
     */
    public function remove(int $index, bool $returnItem=true): mixed
    {
        $res = $this->removeItems($index, 1, $returnItem);
        if ($returnItem) $res = $res[0];

        return $this;
    }

    /**
     * Remove the first occurrence of an item in a collection
     * @param mixed $item The item to remove
     * @return \FranklinEkemezie\PHPAether\Utils\Collection
     */
    public function removeItem(mixed $item): self
    {
        $index = $this->indexOf($item);
        return $this->remove($index, false);
    }

    public function removeItems(int $start=0, ?int $length=null, bool $returnItems=false): array|self
    {
        $size = $this->size();
        $indexLowerBound = 0 - $size;
        $indexUpperBound = $size - 1;
        if ($start < $indexLowerBound || $start > $indexUpperBound)
            throw new \OutOfRangeException('Invalid start index');
        
        $removedItems = array_splice($this->items, $start, $length);

        return $returnItems ? $removedItems : $this;
    }

    /**
     * Remove items from the first occurrence of the start item to the
     * first occurrence of the last item
     * @param mixed $startItem
     * @param mixed $endItem
     * @return \FranklinEkemezie\PHPAether\Utils\Collection
     */
    public function removeItemsFrom(mixed $startItem, mixed $endItem): self
    {
        $startIndex = $this->indexOf($startItem);
        $endIndex = $this->indexOf($endItem);
        $length = $startIndex - $endIndex;

        return $this->removeItems($startIndex, $length, false);
    }

    /**
     * Insert items at the end of the collection
     */
    public function push(mixed ...$items): self
    {
        array_push($this->items, ...$items);
        return $this;
    }

    /**
     * Removes the item at the end of the list
     * @param $returnItem Whether to return the item removed
     * @return mixed Returns the item removed or the object.
     */
    public function pop(bool $returnItem=true): mixed
    {
        return $this->remove(-1, $returnItem);
    }

    /**
     * Insert an item at the beginning of the collection
     * @param mixed $item
     * @return \FranklinEkemezie\PHPAether\Utils\Collection
     */
    public function unshift(mixed ...$items): self
    {
        array_unshift($this->items, ...$items);
        return $this;
    }

    /**
     * Remove item at the start of the list
     * @param bool $returnItem Whether to return the item removed
     * @return mixed Returns the item removed or the collection object
     */
    public function shift(bool $returnItem=false): mixed
    {
        $shiftedItem = array_shift($this->items);
        return $returnItem ? $shiftedItem : $this;
    }

    public function size(): int
    {
        return count($this->items);
    }

    /**
     * Find the index of tthe first occurrence of the item
     * @param mixed $item The item to get the index
     * @return int|null Returns the index of the item or `null` if not found
     */
    public function indexOf(mixed $item): ?int
    {
        return array_search($item, $this->items, true) ?: null;
    }

    /**
     * Cast the collection into an array
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Cast the collection into a JSON
     * @return string
     */
    public function toJSON(): string
    {
        return json_encode($this->items);
    }

    /**
     * Perform an action for each item in the collection
     * @param callable $callBackFn A callback function that accepts two parameters.
     * The first is the array and the second is the key/index.
     * @return \FranklinEkemezie\PHPAether\Utils\Collection
     */
    public function forEach(callable $callBackFn): self
    {
        array_walk($this->items, $callBackFn);
        return $this;
    }

    /**
     * Create a new collection from the given collection based on the callback action
     * @param callable $callBackFn The callback to run for each item.
     * It accepts one parameter which is the current item of each iteration.
     * The callback action should return a value. The value type returned for each 
     * collection item must be consistent, otherwise a new collection cannot be created
     * @return \FranklinEkemezie\PHPAether\Utils\Collection
     */
    public function map(callable $callBackFn): self
    {
        return new static(...array_map($callBackFn, $this->items));
    }


    /**
     * Filter the items in a collection based on some action
     * @param mixed $callBackFn The callback function to determine which item to filter.
     * It takes one parameter - the current item of iteration 
     * The filter callback action must return `true` for an item to pass the filter or `false` to filter
     * it out. If no filter is specified, the empty items are removed.
     * @return \FranklinEkemezie\PHPAether\Utils\Collection
     */
    public function filter(?callable $callBackFn=null): self
    {
        return new static(...array_filter($this->items, $callBackFn));
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