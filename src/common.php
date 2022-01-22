<?php

use xy_jx\Utils\Sundry;

if (!function_exists('orderNo')) {
    function orderNo()
    {
        return Sundry::orderNo();
    }
}
