<?php

declare(strict_types=1);


namespace App\Exceptions\Crawler;


use App\Exceptions\IdentifiableExceptionTrait;

class SelectorNotFoundException extends \RuntimeException implements CrawlerException
{
    use IdentifiableExceptionTrait;
    
    public function __construct(string $template, string $element, string $message)
    {
        $message = sprintf($template, $this->getId(), $element, $message);
        parent::__construct($message);
    }
    
    public static function forSelectorNotFound(string $message, string $element): self
    {
        return new self('ID: %s. Element %s was not found. Message: %s', $element, $message);
    }
}
