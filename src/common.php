<?php

use xy_jx\Utils\Sundry;

if (!function_exists('orderNo')) {
    function orderNo(): string
    {
        return Sundry::orderNo();
    }
}
