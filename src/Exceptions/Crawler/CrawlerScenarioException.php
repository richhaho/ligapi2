<?php

namespace App\Exceptions\Crawler;

use \App\Exceptions\IdentifiableExceptionTrait;

class CrawlerScenarioException extends \RuntimeException implements CrawlerException
{
    use IdentifiableExceptionTrait;

    public function __construct(string $template, string $element, string $message)
    {
        $message = sprintf($template, $this->getId(), $element, $message);
        parent::__construct($message);
    }

    public static function forNotLoggedIn(string $element, ?string $message = 'Not logged in'): self
    {
        return new self('ID: %s. Element %s was not found. Message: %s', $element, $message);
    }

    public static function forProductNotFound(string $element, ?string $message = 'Product not found'): self
    {
        return new self('ID: %s. Element %s was not found. Message: %s', $element, $message);
    }
}
