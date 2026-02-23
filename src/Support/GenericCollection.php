<?php

namespace Dru1x\ExpoPush\Support;

use Countable;
use Dru1x\ExpoPush\PushReceipt\PushReceipt;
use Dru1x\ExpoPush\Support\CollectionMethods;
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
final class GenericCollection implements Collection
{
    /** @use CollectionMethods<TKey, TValue> */
    use CollectionMethods;
}