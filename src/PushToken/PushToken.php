<?php

namespace Dru1x\ExpoPush\PushToken;

use Dru1x\ExpoPush\Support\ConvertsToJson;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;

final readonly class PushToken implements JsonSerializable, Stringable
{
    use ConvertsToJson;

    public function __construct(public string $value)
    {
        if (!preg_match('/^ExponentPushToken\[([a-zA-Z0-9\-_]+)]$/', $this->value)) {
            throw new InvalidArgumentException("'$value' is not a valid push token");
        }
    }

    // Helpers ----

    public function toString(): string
    {
        return $this->value;
    }

    // Internals ----

    public function __toString(): string
    {
        return $this->toString();
    }

    /** @inheritDoc */
    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}