<?php

namespace Dru1x\ExpoPush\PushToken;

use Dru1x\ExpoPush\Support\Collection;
use Dru1x\ExpoPush\Support\CollectionMethods;

/**
 * A collection of PushToken objects
 */
final class PushTokenCollection implements Collection
{
    /** @use CollectionMethods<int, PushToken> */
    use CollectionMethods;

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
