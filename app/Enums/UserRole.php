<?php

namespace App\Enums;

enum UserRole: string
{
    case Patient = 'patient';
    case Admin = 'admin';
}