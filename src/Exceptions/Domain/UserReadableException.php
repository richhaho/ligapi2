<?php

declare(strict_types=1);


namespace App\Exceptions\Domain;


interface UserReadableException
{
    public function getUserMessage(): string;
}
