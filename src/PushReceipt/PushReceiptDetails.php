<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\PushTicket\PushTicketErrorCode;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\ConvertsToJson;
use JsonSerializable;

final readonly class PushReceiptDetails implements JsonSerializable
{
    use ConvertsToJson;

    public function __construct(
        public ?PushToken $expoPushToken,
    ) {}
}