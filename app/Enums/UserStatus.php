<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Restricted = 'restricted';
    case Banned = 'banned';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Active',
            self::Restricted => 'Restricted',
            self::Banned => 'Banned',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'success',    // Bootstrap green
            self::Restricted => 'warning', // Bootstrap yellow/orange
            self::Banned => 'danger',     // Bootstrap red
        };
    }
}