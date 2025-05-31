<?php

namespace Dru1x\ExpoPush\PushError;

final readonly class PushError
{
    public function __construct(
        public PushErrorCode $code,
        public string        $message,
        public ?array        $details = null,
        public ?int          $startIndex = null,
        public ?int          $endIndex = null,
    ) {}
}