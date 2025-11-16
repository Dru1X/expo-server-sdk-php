<?php

namespace Dru1x\ExpoPush\Result;

use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushTicket\PushTicketCollection;
use Dru1x\ExpoPush\Support\Result;

final readonly class SendNotificationsResult extends Result
{
    public function __construct(public PushTicketCollection $tickets, PushErrorCollection $errors)
    {
        parent::__construct($errors);
    }
}