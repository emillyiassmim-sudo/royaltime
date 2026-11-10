<?php

namespace App\Support;

class BrazilianTaxId
{
    public static function normalize(string $taxId): string
    {
        return preg_replace('/\D+/', '', $taxId) ?? '';
    }

    public static function isValid(string $taxId): bool
    {
        $digits = self::normalize($taxId);

        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        for ($position = 9; $position < 11; $position++) {
            $sum = 0;

            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $digits[$index] * (($position + 1) - $index);
            }

            $remainder = ($sum * 10) % 11;
            $digit = $remainder === 10 ? 0 : $remainder;

            if ((int) $digits[$position] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
