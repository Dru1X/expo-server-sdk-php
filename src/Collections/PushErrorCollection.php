<?php

namespace Dru1x\ExpoPush\Collections;

use Dru1x\ExpoPush\Data\PushError;

/**
 * A collection of PushError objects
 *
 * @extends Collection<array-key, PushError>
 */
final class PushErrorCollection extends Collection
{
    public function __construct(PushError ...$errors)
    {
        parent::__construct($errors);
    }
}