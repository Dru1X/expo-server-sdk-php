<?php

namespace Dru1x\ExpoPush\PushToken;

use Dru1x\ExpoPush\Support\Collection;

/**
 * A collection of PushToken objects
 *
 * @extends Collection<array-key, PushToken>
 */
final class PushTokenCollection extends Collection
{
    public function __construct(PushToken ...$pushToken)
    {
        parent::__construct($pushToken);
    }
}