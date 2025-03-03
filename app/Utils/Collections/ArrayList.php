<?php

declare(strict_types=1);

namespace PHPAether\Utils\Collections;

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
     * @param int $offset
     * @param int|null $length
     * @return $this
     */
    public function copy(int $start=0, ?int $length=null): ArrayList
    {
        if ($length < 0) {
            throw new \InvalidArgumentException('Parameter $length must be a positive integer');
        }

        $inReverseMode = $start < 0; // false
        $listSize = $this->size(); // 4
        $length ??= $listSize; // 2
        $startIndex = $this->normaliseIndex($start); // 1

        $getNextIndex = function ($currIndex) use (
            $startIndex, $listSize, $inReverseMode, $length
        ): int|false {
            if (! $inReverseMode) {
                $endIndex = $startIndex + ($length - 1); // 2
                $endIndex = min($endIndex, $listSize - 1); // cap value at maximum index // 2
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

            print_r("Curr: $currIndex\n");

            $resultList[] = $this->list[$currIndex];
            $currIndex = $getNextIndex($currIndex);
        }

        return new static($resultList);
    }

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
     * @return array|array[]|iterable[]
     */
    public function toArray(): array
    {
        return $this->list;
    }

    /**
     * Get the JSON representation of the list
     * @return string
     */
    public function toJSON(): string
    {
        return json_encode($this->toArray());
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
            throw new \OutOfRangeException("List does not have the index: $index");
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

    public function remove(int $index)
    {
        if (! $this->indexInRange($index)) {
            throw new \OutOfRangeException("Index $index is out of range");
        }

        $value = $this->get($index);

        unset($this->list[$this->normaliseIndex($index)]);
        $this->list = array_values($this->list);

        return $value;
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

    private function normaliseIndex(int $index, bool $capAtMaxIndex=false): int
    {
        $maxIndex = $this->size() - 1;
        $normIndex = $index < 0 ? $this->size() + $index : $index;
        if ($capAtMaxIndex) $normIndex = min($normIndex, $maxIndex);

        return $normIndex;
    }

    /**
     * Get a slice of the list
     * @param int|null $start The start index of the slice (inclusive).
     * @param int|null $end The end index of the slice (exclusive)
     * @return $this
     */
    public function slice(?int $start=null, ?int $end=null): static
    {

        if (is_int($start) && ! $this->indexInRange($start)) {
            throw new \OutOfRangeException('$start is out of range');
        }

        if (is_int($end) && ! $this->indexInRange($end)) {
            throw new \OutOfRangeException('$end is out of range');
        }

        $start  = $this->normaliseIndex($start ?? 0);
        $end    = $this->normaliseIndex($end ?? $this->size());
        $length = $end - $start;

        return new static(array_slice($this->list, $start, $length));
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
        if (is_int($start) && ! $this->indexInRange($start)) {
            throw new \OutOfRangeException('$start is out of range');
        }

        if (is_int($end) && ! $this->indexInRange($end)) {
            throw new \OutOfRangeException('$end is out of range');
        }

        $start  = $this->normaliseIndex($start ?? 0);
        $end    = $this->normaliseIndex($end ?? $this->size());
        $length = $end - $start;

        return new static(array_splice($this->list, $start, $length, $replacement));
    }

}