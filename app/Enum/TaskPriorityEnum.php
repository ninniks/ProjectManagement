<?php

namespace App\Enum;

enum TaskPriorityEnum:string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case VeryHigh = 'very high';
}
