<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\Collection;

/**
 * A collection of PushReceipt objects
 *
 * @extends Collection<array-key, PushReceipt>
 */
final class PushReceiptCollection extends Collection
{
    public function __construct(PushReceipt ...$pushReceipt)
    {
        parent::__construct($pushReceipt);
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
        return array_find($this->items, fn(PushReceipt $receipt) => $receipt->id === $receiptId);
    }
}