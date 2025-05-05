<?php

namespace Dru1x\ExpoPush\Data;

use Dru1x\ExpoPush\Collections\RequestErrorCollection;
use Dru1x\ExpoPush\Collections\PushTicketCollection;
use Dru1x\ExpoPush\Collections\PushTokenCollection;
use Dru1x\ExpoPush\Maps\PushTicketMap;
use Exception;

readonly class NotificationDispatch
{
    /**
     * @param PushTicketMap         $tokenTickets
     * @param array<int, Exception> $errors
     */
    public function __construct(
        public PushTicketMap           $tokenTickets,
        public ?RequestErrorCollection $errors = null,
    ) {}

    // Helpers ----

    public function hasErrors(): bool
    {
        return (bool)$this->errors?->count();
    }

    public function getTickets(): PushTicketCollection
    {
        return $this->tokenTickets->getTickets();
    }

    public function getTokens(): PushTokenCollection
    {
        return $this->tokenTickets->getTokens();
    }

    public function getToken(PushTicket $ticket): ?PushToken
    {
        return $this->tokenTickets->getToken($ticket);
    }
}