<?php

namespace xy_jx\Utils;
use Composer\Script\Event;
class Install
{
    /**
     * Install.
     * @param mixed $event
     * @return void
     */
    public static function install(Event $event)
    {
        Encryption::resetKey();
    }
}