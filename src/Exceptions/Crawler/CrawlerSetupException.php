<?php

declare(strict_types=1);


namespace App\Exceptions\Crawler;


use RuntimeException;

class CrawlerSetupException extends RuntimeException implements CrawlerException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
    
    public static function forSetupException(string $message): self
    {
        return new self(sprintf('Crawler setup exception: %s', $message));
    }
}

