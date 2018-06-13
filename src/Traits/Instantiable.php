<?php

namespace CmsPilot\Client\Traits;

trait Instantiable
{
    protected static $instance;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}
