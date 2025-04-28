<?php

namespace Dru1x\ExpoPush\Collections;

use Dru1x\ExpoPush\Data\PushTicket;

/**
 * A collection of PushTicket objects
 *
 * @extends Collection<array-key, PushTicket>
 */
class PushTicketCollection extends Collection
{
    public function __construct(PushTicket ...$pushTickets)
    {
        parent::__construct($pushTickets);
    }
}