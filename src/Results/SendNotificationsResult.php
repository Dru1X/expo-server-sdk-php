<?php

namespace Dru1x\ExpoPush\Results;

use Dru1x\ExpoPush\Collections\PushErrorCollection;
use Dru1x\ExpoPush\Collections\PushTicketCollection;

final readonly class SendNotificationsResult extends Result
{
    public function __construct(public PushTicketCollection $tickets, ?PushErrorCollection $errors = null)
    {
        parent::__construct($errors);
    }
}