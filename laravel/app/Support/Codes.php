<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Codes
{
    /**
     * Generate a sequential, human-friendly code like "AUTO-000123".
     */
    public static function next(string $table, string $prefix, int $pad = 5, string $column = 'code'): string
    {
        $count = DB::table($table)->count() + 1;
        // ensure uniqueness even with soft-deletes / gaps
        do {
            $code = $prefix.'-'.str_pad((string) $count, $pad, '0', STR_PAD_LEFT);
            $exists = DB::table($table)->where($column, $code)->exists();
            $count++;
        } while ($exists);

        return $code;
    }
}
