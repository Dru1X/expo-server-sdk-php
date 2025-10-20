<?php

namespace Dru1x\ExpoPush\Support;

trait ConvertsFromArray
{
    /**
     * Create an object from an array
     */
    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }
}
