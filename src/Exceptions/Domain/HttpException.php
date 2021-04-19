<?php

declare(strict_types=1);


namespace App\Exceptions\Domain;


interface HttpException
{
    public function getStatusCode(): int;
}
