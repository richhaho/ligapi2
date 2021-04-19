<?php

declare(strict_types=1);


namespace App\Entity;


interface LoggableInterface
{
    public function getLogData(): string;
}
