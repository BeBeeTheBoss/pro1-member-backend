<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class GenderNormalizer
{
    /**
     * Convert the gender formats used by older app clients to the values allowed
     * by the users.gender database constraint.
     */
    public static function normalize(mixed $gender): ?string
    {
        if ($gender === null || $gender === '') {
            return null;
        }

        $value = strtolower(trim((string) $gender));

        return match ($value) {
            'male', 'm', '1' => 'male',
            'female', 'f', '0', '2' => 'female',
            default => throw ValidationException::withMessages([
                'gender' => 'Gender must be male or female.',
            ]),
        };
    }
}
