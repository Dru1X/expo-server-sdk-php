<?php

namespace Dru1x\ExpoPush\Data;

use Dru1x\ExpoPush\Enums\RequestErrorCode;

readonly class RequestError
{
    public function __construct(
        public RequestErrorCode $code,
        public string           $message,
        public int|string|null  $index = null,
    ) {}
}