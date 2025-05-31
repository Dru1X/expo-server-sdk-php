<?php

namespace Dru1x\ExpoPush\PushError;

use Dru1x\ExpoPush\Support\Collection;

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