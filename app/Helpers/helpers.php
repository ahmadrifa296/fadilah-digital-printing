<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Helper global untuk mendapatkan nilai setting.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting($key, $default = null)
    {
        return Setting::getVal($key, $default);
    }
}
