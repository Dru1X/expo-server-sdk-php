<?php

namespace Dru1x\ExpoPush\Result;

use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushTicket\PushTicket;
use Dru1x\ExpoPush\PushTicket\PushTicketCollection;
use Dru1x\ExpoPush\Support\Result;

final readonly class SendNotificationsResult extends Result
{
    public function __construct(public PushTicketCollection $tickets, PushErrorCollection $errors)
    {
        parent::__construct($errors);
    }

    public function hasTickets(): bool
    {
        return $this->tickets->count() > 0;
    }

    public function hasSuccessfulTickets(): bool
    {
        $successfulTickets = $this->tickets->filter(fn(PushTicket $ticket) => $ticket->isSuccessful());

        return $successfulTickets->count() > 0;
    }

    public function hasFailedTickets(): bool
    {
        $successfulTickets = $this->tickets->filter(fn(PushTicket $ticket) => $ticket->isFailed());

        return $successfulTickets->count() > 0;
    }
}