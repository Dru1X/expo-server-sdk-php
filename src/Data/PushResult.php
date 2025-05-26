<?php

namespace Dru1x\ExpoPush\Data;

use Dru1x\ExpoPush\Collections\PushErrorCollection;
use Dru1x\ExpoPush\Collections\PushTicketCollection;

final readonly class PushResult
{
    public function __construct(
        public PushTicketCollection $tickets,
        public ?PushErrorCollection $errors = null,
    ) {}

    // Helpers ----

    public function hasErrors(): bool
    {
        return (bool)$this->errors?->count();
    }
}