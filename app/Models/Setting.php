<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    protected static function cacheKey(string $key): string
    {
        return 'setting.' . $key;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::cacheKey($key);
        $value = Cache::get($cacheKey);
        if ($value !== null) {
            return $value;
        }
        $row = self::where('key', $key)->first();
        $value = $row ? $row->value : $default;
        Cache::put($cacheKey, $value, 3600);
        return $value;
    }

    public static function set(string $key, ?string $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::cacheKey($key));
    }
}
