<?php

namespace Dru1x\ExpoPush\PushTicket;

use Countable;
use Dru1x\ExpoPush\Support\Collection;
use IteratorAggregate;
use JsonSerializable;

/**
 * A collection of PushTicket objects
 */
final class PushTicketCollection implements Countable, IteratorAggregate, JsonSerializable
{
    /** @use Collection<int, PushTicket> */
    use Collection;

    public function __construct(PushTicket ...$pushTickets)
    {
        $this->items = $pushTickets;
    }
}