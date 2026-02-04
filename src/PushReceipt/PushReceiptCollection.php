<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Countable;
use Dru1x\ExpoPush\Support\Collection;
use IteratorAggregate;
use JsonSerializable;

/**
 * A collection of PushReceipt objects
 */
final class PushReceiptCollection implements Countable, IteratorAggregate, JsonSerializable
{
    /** @use Collection<int, PushReceipt> */
    use Collection;

    public function __construct(PushReceipt ...$pushReceipts)
    {
        $this->items = $pushReceipts;
    }

    // Helpers ----

    /**
     * Find a push receipt by its ID
     *
     * @param string $receiptId
     *
     * @return PushReceipt|null
     */
    public function getById(string $receiptId): ?PushReceipt
    {
        foreach ($this->items as $receipt) {
            if ($receipt->id === $receiptId) {
                return $receipt;
            }
        }

        return null;
    }
}