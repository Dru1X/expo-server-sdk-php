<?php

namespace Dru1x\ExpoPush\PushToken;

use Dru1x\ExpoPush\Support\ConvertsFromJson;
use Dru1x\ExpoPush\Support\ConvertsToJson;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;

final readonly class PushToken implements JsonSerializable, Stringable
{
    use ConvertsFromJson, ConvertsToJson;

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

    public static function fromString(string $value): self
    {
        return new self($value);
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

    public static function fromJson(?string $json): self
    {
        return self::fromString(
            self::jsonDecode($json)
        );
    }
}
