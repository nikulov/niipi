<?php

use Illuminate\Support\Carbon;

if (!function_exists('__date')) {
    function __date($date = null)
    {
        return $date ? Carbon::parse($date)->format('d.m.Y') : '';
    }
}
