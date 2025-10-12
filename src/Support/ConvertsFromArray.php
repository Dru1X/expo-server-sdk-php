<?php

namespace Dru1x\ExpoPush\Support;

trait ConvertsFromArray
{
    /**
     * Create an object from an array
     */
    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
