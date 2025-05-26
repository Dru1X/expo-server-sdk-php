<?php

namespace Dru1x\ExpoPush\Collections;

/**
 * A collection of push receipt IDs
 *
 * @extends Collection<array-key, string>
 */
final class PushReceiptIdCollection extends Collection
{
    public function __construct(string ...$pushReceiptId)
    {
        parent::__construct($pushReceiptId);
    }
}