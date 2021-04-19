<?php

declare(strict_types=1);


namespace App\Exceptions\Crawler;


use App\Exceptions\IdentifiableExceptionTrait;
use RuntimeException;

class CrawlerOtherException extends RuntimeException implements CrawlerException
{
    use IdentifiableExceptionTrait;
    
    public function __construct(string $template, string $element, string $message, string $exceptionMessage)
    {
        $message = sprintf($template, $this->getId(), $element, $exceptionMessage, $message);
        parent::__construct($message);
    }
    
    public static function forOtherException(string $message, string $element, string $exceptionMessage): self
    {
        return new self('ID: %s. Element: %s. Error: %s. Message: %s.', $element, $message, $exceptionMessage);
    }
}
