<?php

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\Data\PushToken;
use Dru1x\ExpoPush\Enums\PushStatus;

final readonly class FailedPushTicket extends PushTicket
{
    public function __construct(PushToken $token, protected string $message, protected PushTicketDetails $details)
    {
        parent::__construct($token, PushStatus::Error);
    }
}