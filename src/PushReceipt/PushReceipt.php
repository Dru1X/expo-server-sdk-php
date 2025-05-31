<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\ConvertsToJson;
use Dru1x\ExpoPush\Support\PushStatus;
use JsonSerializable;

abstract readonly class PushReceipt implements JsonSerializable
{
    use ConvertsToJson;

    public function __construct(
        public string     $id,
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