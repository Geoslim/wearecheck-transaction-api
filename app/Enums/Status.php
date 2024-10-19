<?php

namespace App\Enums;

enum Status: string
{
    case FAILED = 'failed';
    case SUCCESS = 'success';
    case PENDING = 'pending';
}
