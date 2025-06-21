<?php

namespace App\Enums;

enum TaskStatus: int
{
    case PENDING = 1;
    case IN_PROGRESS = 2;
    case COMPLETED = 3;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
