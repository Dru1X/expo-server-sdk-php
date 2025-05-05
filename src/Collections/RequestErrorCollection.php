<?php

namespace Dru1x\ExpoPush\Collections;

use Dru1x\ExpoPush\Data\RequestError;

/**
 * A collection of RequestError objects
 *
 * @extends Collection<array-key, RequestError>
 */
class RequestErrorCollection extends Collection
{
    public function __construct(RequestError ...$errors)
    {
        parent::__construct($errors);
    }
}