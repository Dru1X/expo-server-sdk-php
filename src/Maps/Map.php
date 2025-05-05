<?php

namespace Dru1x\ExpoPush\Maps;

use Countable;
use Dru1x\ExpoPush\Traits\ConvertsToJson;
use IteratorAggregate;
use JsonSerializable;
use SplObjectStorage;
use Traversable;

/**
 * An abstract map class with some useful helpers
 *
 * @template TKey of object
 * @template TValue
 */
abstract class Map implements Countable, IteratorAggregate, JsonSerializable
{
    use ConvertsToJson;

    /**
     * The items contained in the map
     *
     * @var SplObjectStorage<TKey, TValue>
     */
    protected SplObjectStorage $items;

    /**
     * @param SplObjectStorage<TKey, TValue> $items
     */
    public function __construct(?SplObjectStorage $items = null)
    {
        $this->items = $items ?? new SplObjectStorage();
    }

    // Helpers ----

    /**
     * Get the size of this map
     *
     * @return int
     */
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * Add an item to this map with the given key object
     *
     * @param TKey&object $key
     * @param TValue      $item
     *
     * @return void
     */
    public function add(object $key, mixed $item): void
    {
        $this->items->attach($key, $item);
    }

    /**
     * Iterate over this map, passing each entry to the given callable
     *
     * @param callable(TKey, TValue):void $callable
     *
     * @return void
     */
    public function each(callable $callable): void
    {
        $this->items->rewind();

        while ($this->items->valid()) {
            $callable($this->items->current(), $this->items->getInfo());
            $this->items->next();
        }

        $this->items->rewind();
    }

    /**
     * Convert this map to an array
     *
     * @return array<int, array<int, TKey|TValue>>
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->items as $key) {
            $array[] = [$key, $this->items[$key]];
        }

        return $array;
    }

    // Internals ----

    /**
     * @return SplObjectStorage<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return $this->items;
    }
}