<?php

declare(strict_types=1);


namespace App\EventListener;


use App\Exceptions\Api\ValidationException;
use Throwable;

class ErrorListener extends \Symfony\Component\HttpKernel\EventListener\ErrorListener
{
    protected function logException(Throwable $exception, string $message): void
    {
        if (!$exception instanceof ValidationException) {
            parent::logException($exception, $message);
        }
    }
}
