<?php

namespace Dru1x\ExpoPush\Results;

use Dru1x\ExpoPush\Collections\PushTicketCollection;
use Dru1x\ExpoPush\PushError\PushErrorCollection;

final readonly class SendNotificationsResult extends Result
{
    public function __construct(public PushTicketCollection $tickets, ?PushErrorCollection $errors = null)
    {
        parent::__construct($errors);
    }
}