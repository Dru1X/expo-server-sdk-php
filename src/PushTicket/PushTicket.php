<?php

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\ConvertsToJson;
use Dru1x\ExpoPush\Support\PushStatus;
use JsonSerializable;

abstract readonly class PushTicket implements JsonSerializable
{
    use ConvertsToJson;

    public function __construct(
        public PushToken  $token,
        public PushStatus $status,
    ) {}

    // Internals ----

    /** @inheritDoc */
    public function jsonSerialize(): array
    {
        return array_filter(
            get_object_vars($this)
        );
    }

}