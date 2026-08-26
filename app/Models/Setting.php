<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Helper untuk mendapatkan nilai setting berdasarkan key.
     */
    public static function getVal($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Helper untuk menyimpan/mengubah setting.
     */
    public static function setVal($key, $value, $description = null)
    {
        $data = [
            'value' => $value,
            'description' => $description
        ];

        if ($key === 'company_logo') {
            $data['company_logo'] = $value;
        }

        return self::updateOrCreate(
            ['key' => $key],
            $data
        );
    }
}
