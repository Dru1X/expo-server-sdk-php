<?php

namespace Dru1x\ExpoPush\Support;

use Countable;
use IteratorAggregate;
use JsonSerializable;

interface Collection extends Countable, IteratorAggregate, JsonSerializable
{

}