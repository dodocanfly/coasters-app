<?php

namespace App\Validation;

class DateTimeRules
{
    public static function valid_time(string $time): bool
    {
        if (preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/', $time) === 1) {
            return true;
        }

        return false;
    }
}
