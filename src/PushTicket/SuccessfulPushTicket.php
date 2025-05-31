<?php

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\Enums\PushStatus;
use Dru1x\ExpoPush\PushToken\PushToken;

final readonly class SuccessfulPushTicket extends PushTicket
{
    public function __construct(PushToken $token, public string $receiptId)
    {
        parent::__construct($token, PushStatus::Ok);
    }
}