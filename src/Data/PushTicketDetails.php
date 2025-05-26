<?php

namespace Dru1x\ExpoPush\Data;

use Dru1x\ExpoPush\Enums\PushTicketErrorCode;
use Dru1x\ExpoPush\Traits\ConvertsToJson;
use JsonSerializable;

final readonly class PushTicketDetails implements JsonSerializable
{
    use ConvertsToJson;

    public function __construct(
        public PushTicketErrorCode $error,
        public ?PushToken          $expoPushToken,
    ) {}
}