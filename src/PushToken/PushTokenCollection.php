<?php

namespace Dru1x\ExpoPush\PushToken;

use Countable;
use Dru1x\ExpoPush\Support\Collection;
use IteratorAggregate;
use JsonSerializable;

/**
 * A collection of PushToken objects
 */
final class PushTokenCollection implements Countable, IteratorAggregate, JsonSerializable
{
    /** @use Collection<int, PushToken> */
    use Collection;

    public function __construct(PushToken ...$pushTokens)
    {
        $this->items = $pushTokens;
    }

    public static function fromArray(array $data): static
    {
        $tokens = array_map(PushToken::fromString(...), $data);

        return new self(...$tokens);
    }
}
