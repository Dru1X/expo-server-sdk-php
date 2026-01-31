<?php

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\Support\Collection;

/**
 * A collection of PushTicket objects
 *
 * @extends Collection<PushTicket>
 */
final class PushTicketCollection extends Collection
{
    public function __construct(PushTicket ...$pushTickets)
    {
        self::fromIterable($pushTickets);
    }
}