<?php

namespace Dru1x\ExpoPush\PushMessage;

enum Priority: string
{
    case Default = 'default';
    case Normal = 'normal';
    case High = 'high';
}
