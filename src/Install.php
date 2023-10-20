<?php

namespace xy_jx\Utils;

class Install
{
    /**
     * Install.
     * @param mixed $event
     * @return void
     */
    public static function install($event)
    {
        Encryption::resetKey();
        Jwt::resetKey();
    }
}