<?php

namespace Dru1x\ExpoPush\PushError;

use Countable;
use Dru1x\ExpoPush\Support\Collection;
use IteratorAggregate;
use JsonSerializable;

/**
 * A collection of PushError objects
 */
final class PushErrorCollection implements Countable, IteratorAggregate, JsonSerializable
{
    /** @use Collection<int, PushError> */
    use Collection;

    public function __construct(PushError ...$errors)
    {
        $this->items = $errors;
    }
}