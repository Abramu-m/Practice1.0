<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $val = static::where('key', $key)->value('value');
        return $val !== null ? $val : $default;
    }

    public static function set(string $key, mixed $value, string $description = null): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            array_filter(['value' => $value, 'description' => $description], fn($v) => $v !== null)
        );
    }
}
