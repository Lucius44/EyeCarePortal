<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Restricted = 'restricted';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Active',
            self::Restricted => 'Restricted',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'success', // Bootstrap green
            self::Restricted => 'danger', // Bootstrap red
        };
    }
}