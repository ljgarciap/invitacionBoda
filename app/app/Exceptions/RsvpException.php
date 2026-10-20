<?php

namespace App\Exceptions;

use DomainException;

class RsvpException extends DomainException
{
    public static function closed(): self
    {
        return new self('El plazo para confirmar ya cerró.');
    }

    public static function tooManyAttendees(int $seats): self
    {
        return new self("Esta invitación tiene {$seats} cupo(s) asignado(s).");
    }

    public static function namesMismatch(): self
    {
        return new self('Debe haber un nombre por cada asistente.');
    }
}
