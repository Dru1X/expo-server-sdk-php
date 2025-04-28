<?php

namespace Dru1x\ExpoPush\Data;

use Dru1x\ExpoPush\Enums\PushStatus;
use Dru1x\ExpoPush\Traits\ConvertsToJson;
use JsonSerializable;

readonly class PushReceipt implements JsonSerializable
{
    use ConvertsToJson;

    public function __construct(
        public string     $id,
        public PushStatus $status,
        public ?string    $message = null,
        public ?array     $details = null,
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