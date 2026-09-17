<?php

namespace App\Support;

use App\Models\SystemSetting;

class Fmt
{
    protected static ?string $symbol = null;

    public static function symbol(): string
    {
        if (self::$symbol === null) {
            self::$symbol = SystemSetting::get('localization', 'currency_symbol', '₹') ?: '₹';
        }
        return self::$symbol;
    }

    public static function money($amount, bool $withSymbol = true): string
    {
        $n = number_format((float) $amount, 2);
        return $withSymbol ? self::symbol().$n : $n;
    }

    public static function moneyShort($amount): string
    {
        $a = (float) $amount;
        $s = self::symbol();
        if (abs($a) >= 10000000) return $s.number_format($a / 10000000, 2).'Cr';
        if (abs($a) >= 100000) return $s.number_format($a / 100000, 2).'L';
        if (abs($a) >= 1000) return $s.number_format($a / 1000, 1).'K';
        return $s.number_format($a, 0);
    }

    public static function duration(int $seconds): string
    {
        if ($seconds < 60) return $seconds.'s';
        if ($seconds < 3600) return floor($seconds / 60).'m '.($seconds % 60).'s';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        return $h.'h '.$m.'m';
    }
}
