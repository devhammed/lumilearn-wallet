<?php

namespace App\Enums;

enum Currency: string
{
    case USD = 'USD';

    public function getSubunit(): int
    {
        return match ($this) {
            self::USD => 100,
        };
    }

    public function toDatabaseAmount(float $value): int
    {
        return $value * $this->getSubunit();
    }

    public function fromDatabaseAmount(int $value): float
    {
        return $value / $this->getSubunit();
    }
}
