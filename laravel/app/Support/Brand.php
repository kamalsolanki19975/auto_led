<?php

namespace App\Support;

use App\Models\SystemSetting;

class Brand
{
    protected static ?array $branding = null;

    protected static function branding(): array
    {
        if (static::$branding === null) {
            static::$branding = array_merge([
                'name' => 'AutoAds Network',
                'tagline' => 'Turn Every Auto Ride Into an Advertising Opportunity',
                'logo_path' => null,
            ], SystemSetting::group('branding'));
        }

        return static::$branding;
    }

    public static function name(): string
    {
        $n = static::branding()['name'] ?? null;
        return $n !== null && $n !== '' ? $n : 'AutoAds Network';
    }

    public static function tagline(): string
    {
        return static::branding()['tagline'] ?? '';
    }

    public static function logo(): ?string
    {
        $p = static::branding()['logo_path'] ?? null;
        return $p ? asset('storage/'.$p) : null;
    }

    public static function initials(): string
    {
        $name = static::name();
        $parts = preg_split('/\s+/', trim($name));
        $letters = collect($parts)->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
        return $letters ?: 'A';
    }

    /** Short mono lockup e.g. AUTOADS·NET from "AutoAds Network". */
    public static function wordmark(): array
    {
        $parts = preg_split('/\s+/', trim(static::name()));
        $first = mb_strtoupper($parts[0] ?? 'AUTOADS');
        $second = isset($parts[1]) ? mb_strtoupper(mb_substr($parts[1], 0, 3)) : '';
        return [$first, $second];
    }

    public static function social(): array
    {
        return SystemSetting::group('social');
    }

    public static function contact(): array
    {
        return SystemSetting::group('company');
    }
}
