<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Countable;
use Dru1x\ExpoPush\Support\Collection;
use IteratorAggregate;
use JsonSerializable;

/**
 * A collection of push receipt IDs
 */
final class PushReceiptIdCollection implements Countable, IteratorAggregate, JsonSerializable
{
    /** @use Collection<int, string> */
    use Collection;

    public function __construct(string ...$pushReceiptIds)
    {
        $this->items = $pushReceiptIds;
    }
}