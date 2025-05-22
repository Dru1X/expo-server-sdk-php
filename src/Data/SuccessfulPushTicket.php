<?php

namespace Dru1x\ExpoPush\Data;

use Dru1x\ExpoPush\Enums\PushStatus;

final readonly class SuccessfulPushTicket extends PushTicket
{
    public function __construct(PushToken $token, public string $receiptId)
    {
        parent::__construct($token, PushStatus::Ok);
    }
}