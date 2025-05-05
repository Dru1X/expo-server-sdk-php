<?php

namespace Dru1x\ExpoPush\Maps;

use Dru1x\ExpoPush\Collections\PushTicketCollection;
use Dru1x\ExpoPush\Collections\PushTokenCollection;
use Dru1x\ExpoPush\Data\PushTicket;
use Dru1x\ExpoPush\Data\PushToken;

/**
 * A map of push tickets to push tokens
 *
 * @extends Map<PushTicket, PushToken>
 */
class PushTicketMap extends Map
{
    /**
     * Get a collection of push tickets from this map
     *
     * @return PushTicketCollection
     */
    public function getTickets(): PushTicketCollection
    {
        $tickets = new PushTicketCollection();

        $this->each(function (PushTicket $ticket) use ($tickets): void {
            $tickets->add($ticket);
        });

        return $tickets;
    }

    /**
     * Get a collection of push tokens from this map
     *
     * @return PushTokenCollection
     */
    public function getTokens(): PushTokenCollection
    {
        $tokens = new PushTokenCollection();

        $this->each(function (PushTicket $ticket, PushToken $token) use ($tokens): void {
            $tokens->add($token);
        });

        return $tokens;
    }

    /**
     * Get the token that corresponds to the given ticket
     *
     * @param PushTicket $ticket
     *
     * @return PushToken|null
     */
    public function getToken(PushTicket $ticket): ?PushToken
    {
        return $this->items[$ticket] ?? null;
    }
}