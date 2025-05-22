<?php

namespace Dru1x\ExpoPush\Collections;

use Dru1x\ExpoPush\Data\PushToken;

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