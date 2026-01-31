<?php

namespace Dru1x\ExpoPush\PushToken;

use Dru1x\ExpoPush\Support\Collection;

/**
 * A collection of PushToken objects
 *
 * @extends Collection<int, PushToken>
 */
final class PushTokenCollection extends Collection
{
    public function __construct(PushToken ...$items)
    {
        $this->items = $items;
    }

    public static function fromArray(array $data): static
    {
        $tokens = array_map(PushToken::fromString(...), $data);

        return new self(...$tokens);
    }
}
