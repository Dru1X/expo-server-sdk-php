<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\Collection;

/**
 * A collection of push receipt IDs
 *
 * @extends Collection<int, string>
 */
final class PushReceiptIdCollection extends Collection
{
    public function __construct(string ...$pushReceiptIds)
    {
        $this->items = $pushReceiptIds;
    }
}