<?php

declare(strict_types=1);


namespace App\Event;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Log
{
    public $logKey = null;
}
