<?php

use xy_jx\Utils\Sundry;

if (!function_exists('order_no')) {
    /**
     * 生成一个订单号
     * @return string
     */
    function order_no(): string
    {
        return Sundry::orderNo();
    }
}
