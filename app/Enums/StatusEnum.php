<?php

namespace App\Enums;

enum StatusEnum: int
{
    case Created = 1;
    case Processing = 2;
    case Pending = 3;
    case Completed = 4;
    case Failed = 5;

    public function label(): string
    {
        return match ($this) {
            self::Created => 'created',
            self::Processing => 'processing',
            self::Pending => 'pending',
            self::Completed => 'completed',
            self::Failed => 'failed',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromLabel(string $label): self
    {
        return match (strtolower($label)) {
            'created' => self::Created,
            'processing' => self::Processing,
            'pending' => self::Pending,
            'completed' => self::Completed,
            'failed' => self::Failed,
            default => throw new \ValueError("$label is not a valid status label"),
        };
    }
}
