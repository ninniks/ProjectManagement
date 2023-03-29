<?php

namespace App\Enum;

enum TaskStatusEnum:string
{
    case Open = 'open';
    case Closed = 'closed';
    case Blocked = 'blocked';
}
