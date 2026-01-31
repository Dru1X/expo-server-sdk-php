<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\Collection;

/**
 * A collection of PushReceipt objects
 *
 * @extends Collection<int, PushReceipt>
 */
final class PushReceiptCollection extends Collection
{
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