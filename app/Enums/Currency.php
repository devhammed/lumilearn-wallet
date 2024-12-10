<?php

namespace App\Enums;

enum Currency: string
{
    case USD = 'USD';

    public function getSubunit(): int
    {
        return 100;
    }
}
