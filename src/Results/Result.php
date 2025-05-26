<?php

namespace Dru1x\ExpoPush\Results;

use Dru1x\ExpoPush\Collections\PushErrorCollection;

abstract readonly class Result
{
    public function __construct(public ?PushErrorCollection $errors = null) {}

    // Helpers ----

    public function hasErrors(): bool
    {
        return (bool)$this->errors?->count();
    }
}