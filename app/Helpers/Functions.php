<?php

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        return \App\Models\WebsiteSetting::get($key, $default);
    }
}
