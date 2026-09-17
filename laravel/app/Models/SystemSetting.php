<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $guarded = [];

    public static function get(string $group, string $key, $default = null)
    {
        $row = static::where('group', $group)->where('key', $key)->first();
        return $row ? $row->value : $default;
    }

    public static function put(string $group, string $key, $value, string $type = 'string'): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value, 'type' => $type]
        );
    }

    public static function group(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }
}
