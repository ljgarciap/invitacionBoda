<?php

namespace App\Enums;

enum GuestStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Accepted => 'Confirmó',
            self::Declined => 'No asistirá',
        };
    }
}
