<?php

namespace Dru1x\ExpoPush\Collections;

use Dru1x\ExpoPush\Data\PushMessage;

/**
 * A collection of PushMessage objects
 *
 * @extends Collection<array-key, PushMessage>
 */
class PushMessageCollection extends Collection
{
    public function __construct(PushMessage ...$pushMessages)
    {
        parent::__construct($pushMessages);
    }
}