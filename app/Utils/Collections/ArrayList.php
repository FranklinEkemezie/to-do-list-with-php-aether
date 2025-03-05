<?php

declare(strict_types=1);

namespace PHPAether\Utils\Collections;

use InvalidArgumentException;
use OutOfRangeException;

class ArrayList
{

    /**
     * @var array|array[]|iterable[] The list
     */
    protected array $list;

    public function __construct(
        iterable $items=[]
    )
    {
        $this->list = array_values([...$items]);
    }

    /**
     * Execute a callback for each of the item in the list
     * @param callable $callback
     * @return void
     */
    public function forEach(callable $callback): void
    {
        array_walk($this->list, function ($value, $key) use ($callback) {
            $callback($value, $key);
        });
    }

    /**
     * Create a new list by mapping over the elements of the list
     * @param callable|null $callback The callback function. It
     * receives the current item value and index
     * @return $this
     */
    public function map(?callable $callback=null): ArrayList
    {
        return new static(array_map(
            fn($value, $index) => ($callback ?? fn($value, $index) => $value)($value, $index),
            $this->list, array_keys($this->list)
        ));
    }


    /**
     * Get a copy of the list
     * @param int $start The index to start the copy from. If not specified
     * the copy starts from the first item. If `$start` is negative, the copy is
     * done in a reverse manner.
     * @param int|null $length The length of the new list copy. If not specified
     * or there are no more items to copy from the start, the copy continues to
     * the last item.
     * @return $this
     */
    public function copy(int $start=0, ?int $length=null): ArrayList
    {
        if (is_int($length) && $length < 0) {
            throw new InvalidArgumentException('Parameter $length must be a positive integer');
        }

        $inReverseMode = $start < 0;
        $listSize = $this->size();
        $length ??= $listSize;
        $startIndex = $this->normaliseIndex($start);

        $getNextIndex = function ($currIndex) use (
            $startIndex, $listSize, $inReverseMode, $length
        ): int|false {
            if (! $inReverseMode) {
                $endIndex = $startIndex + ($length - 1);
                $endIndex = min($endIndex, $listSize - 1); // cap value at maximum index
                $shouldContinue = $currIndex < $endIndex;
            } else {
                $endIndex = $startIndex - ($length - 1);
                $endIndex = max($endIndex, 0); // cap value at minimum index
                $shouldContinue = $currIndex > $endIndex;
            }

            if (! $shouldContinue) return false;

            return ! $inReverseMode ? ++$currIndex : --$currIndex;
        };

        $resultList = [];
        $currIndex = $startIndex;
        while ($currIndex !== false) {
            $resultList[] = $this->list[$currIndex];
            $currIndex = $getNextIndex($currIndex);
        }

        return new static($resultList);
    }

    /**
     * Reverse the items in the list
     * @return $this
     */
    public function reverse(): ArrayList
    {
        return $this->copy(-1);
    }

    /**
     * Reduce the list iteratively to a single value using a callback function
     * @param callable $callback The callback function. It receives `$carry` - the
     * return value of the previous iteration; `$item` - the current iteration value;
     * and `$index` - the index of the current iteration
     * @param mixed|null $initial
     * @return mixed
     */
    public function reduce(callable $callback, mixed $initial=null): mixed
    {
        $index = 0;
        return array_reduce($this->list, function ($carry, $item) use ($callback, &$index) {
            return $callback($carry, $item, $index++);
        }, $initial);
    }

    /**
     * Get the list as a PHP indexed array
     * @return array|array[]|iterable[] the list as an array
     */
    public function toArray(): array
    {
        return $this->list;
    }


    /**
     * @param int $flags Optional flags specified as bitmaps by one or
     * more of the `JSON_*` constants which determines how the value
     * is encoded
     * @param int $depth Set the maximum depth. Must be greater than zero.
     * @return string|false a JSON encoded string on success or FALSE on failure.
     */
    public function toJSON(int $flags=0, int $depth=512): string|false
    {
        return json_encode($this->toArray(), $flags, $depth);
    }

    /**
     * Get the size of the list
     * @return int
     */
    public function size(): int
    {
        return count($this->list);
    }

    /**
     * Determine whether the item is in the list
     * @param int $index
     * @return bool
     */
    public function has(int $index): bool
    {
        return $this->indexInRange($index);
    }

    /**
     * Determine whether the list contains a value
     * @param mixed $value
     * @return bool
     */
    public function includes(mixed $value): bool
    {
        return $this->some(fn($val, $i) => $value === $val);
    }

    /**
     * Get an item at the specified index
     * @param int $index The index of the item
     * @param callable|null $onNotFound The callback fired when no item
     * is found at the specified index. If not specified, NULL is returned
     * when an item is not found
     * @return mixed
     */
    public function get(int $index, ?callable $onNotFound=null): mixed
    {
        if (! $this->has($index)) {
            return ($onNotFound ?? fn() => null)($this, $index);
        }

        return $this->list[$this->normaliseIndex($index)];
    }

    /**
     * Determine whether every item in the list satisfies the given condition
     * @param callable $condition A callback function which should return a
     * boolean. It receives the current iteration value and the index
     * @return bool
     */
    public function every(callable $condition): bool
    {
        return $this->reduce(function ($carry, $value, $key) use ($condition) {
            return $carry && $condition($value, $key);
        }, true);
    }

    /**
     * Determine whether some (one or more) items on the list satisfies the given condition
     * @param callable $condition A callback function that should return a boolean. It
     * receives the current iteration value and the index
     * @return bool
     */
    public function some(callable $condition): bool
    {
        return $this->reduce(function ($carry, $value, $index) use ($condition) {
            return $carry || $condition($value, $index);
        }, false);
    }

    /**
     * Filter the items of a list using the given filter condition
     * @param callable|null $filter The filter condition. It receives
     * the current iteration value and index; and should return a boolean
     * @return $this
     */
    public function filter(?callable $filter=null): ArrayList
    {
        return new static(
            array_filter($this->list, function ($value, $index) use ($filter) {
                return ($filter ?? fn($value, $index) => (bool) $value)($value, $index);
            }, ARRAY_FILTER_USE_BOTH)
        );
    }

    /**
     * Add one or more items to the end of the list
     * @param mixed $values
     * @return $this
     */
    public function append(mixed ...$values): static
    {
        array_push($this->list, ...$values);
        return $this;
    }

    /**
     * Add one or more items to the start of the list
     * @param mixed $values
     * @return $this
     */
    public function prepend(mixed ...$values): static
    {
        array_unshift($this->list, ...$values);
        return $this;
    }

    /**
     * Set the value of an item at a given index
     * @param int $index
     * @param mixed $value
     * @return $this
     */
    public function set(int $index, mixed $value): static
    {
        if (! $this->has($index)) {
            throw new OutOfRangeException("List does not have the index: $index");
        }

        $this->list[$index] = $value;
        return $this;
    }

    /**
     * Insert an item at the specified index.
     * @param int $index
     * @param mixed $value
     * @return $this
     */
    public function insert(int $index, mixed $value): static
    {
        $index = $this->normaliseIndex($index);
        $left = array_slice($this->list, 0, $index);
        $right = array_slice($this->list, $index);

        $this->list = [...$left, $value, ...$right];
        return $this;
    }

    /**
     * Pop the element at the end of the list
     * @return mixed Returns the item removed or NULL if the array
     * is empty
     */
    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            return null;
        }
        return array_pop($this->list);
    }

    /**
     * Shift off an element at the beginning of the list
     * @return mixed Returns the value removed or NULL if
     * the array is empty
     */
    public function shift(): mixed
    {
        if ($this->isEmpty()) return null;
        return array_shift($this->list);
    }

    /**
     * Determines whether the array is empty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->size() === 0;
    }

    /**
     * Remove an item from the list
     * @param int $index The index of the item to remove from the list
     * @return mixed The item removed from the list
     */
    public function remove(int $index): mixed
    {
        if (! $this->indexInRange($index)) {
            throw new OutOfRangeException("Index $index is out of range");
        }

        $removedItems = array_splice($this->list, $index, 1);

        return $removedItems[0];
    }

    /**
     * Determine whether the index specified is in range for the list.
     * An index is in range if it is between the interval `[-($size - 1), $size]`
     * where $size is the size of the list
     * @param int $index
     * @return bool
     */
    private function indexInRange(int $index): bool
    {
        $index = $this->normaliseIndex($index);
        $listSize = $this->size();
        $maxIndex = $listSize - 1;
        $minIndex = 0 - $listSize;

        return ($index >= $minIndex) && ($index <= $maxIndex);
    }

    /**
     * Normalises the index
     * @param int $index
     * @return int The normalised index
     */
    private function normaliseIndex(int $index): int
    {
        return $index < 0 ? ($this->size() + $index) : $index;
    }

    /**
     * Get the offset and length from the start and end
     * values (for slice and splice operations)
     * @param int|null $start The start index
     * @param int|null $end The end index
     * @return array An array containing the offset and length values
     */
    private function getOffsetAndLengthFromStartAndEnd(?int $start=null, ?int $end=null): array
    {
        if (is_int($start) && ! $this->indexInRange($start)) {
            throw new OutOfRangeException('$start is out of range');
        }

        if (is_int($end) && ! $this->indexInRange($end)) {
            throw new OutOfRangeException('$end is out of range');
        }

        $start  = $this->normaliseIndex($start ?? 0);
        $end    = $this->normaliseIndex($end ?? $this->size());

        $offset = $start;
        $length = $end - $start;

        return [$offset, $length];
    }

    /**
     * Get a slice of the list
     * @param int|null $start The start index of the slice (inclusive).
     * @param int|null $end The end index of the slice (exclusive)
     * @return $this
     */
    public function slice(?int $start=null, ?int $end=null): static
    {

        [$offset, $length] = $this->getOffsetAndLengthFromStartAndEnd($start, $end);
        return new static(array_slice($this->list, $offset, $length));
    }

    /**
     * Remove a portion of the list and replace with
     * @param int|null $start
     * @param int|null $end
     * @param array $replacement
     * @return $this
     */
    public function splice(?int $start=null, ?int $end=null, array $replacement=[]): static
    {
        [$offset, $length] = $this->getOffsetAndLengthFromStartAndEnd($start, $end);
        return new static(array_splice($this->list, $offset, $length, $replacement));
    }

}