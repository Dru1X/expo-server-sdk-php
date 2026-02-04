<?php

namespace Dru1x\ExpoPush\Support;

use Countable;
use Dru1x\ExpoPush\PushReceipt\PushReceipt;
use Dru1x\ExpoPush\Support\Collection;
use IteratorAggregate;
use JsonSerializable;

/**
 * A collection of PushReceipt objects
 *
 * @template TKey of array-key
 * @template TValue of mixed
 *
 * @implements IteratorAggregate<TKey, TValue>
 */
final class GenericCollection implements Countable, IteratorAggregate, JsonSerializable
{
    /** @use Collection<TKey, TValue> */
    use Collection;
}