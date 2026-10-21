<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Ambil semua pengaturan sebagai array key => value.
     */
    public static function all_map(): array
    {
        try {
            if (! Schema::hasTable('settings')) {
                return [];
            }

            return static::query()->pluck('value', 'key')->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $map = static::all_map();

        return $map[$key] ?? $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
