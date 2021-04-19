<?php

declare(strict_types=1);


namespace App\Entity\Data;


use App\Exceptions\Domain\InvalidArgumentException;

class PdfFieldBorder
{
    private const VALUES = ['all', 'none', 'bottom'];
    
    private ?string $value = null;
    
    private function __construct(string $value)
    {
        if (!in_array($value, self::VALUES)) {
            throw InvalidArgumentException::forInvalidElement($value, implode('/', self::VALUES));
        }
        $this->value = $value;
    }
    
    public static function all(): self
    {
        return new self('all');
    }
    
    public static function none(): self
    {
        return new self('none');
    }
    
    public static function bottom(): self
    {
        return new self('bottom');
    }
    
    public function getValue(): ?string
    {
        return $this->value;
    }
    
    public function __toString(): string
    {
        return $this->getValue() ?? 'none';
    }
    
    public static function fromString($string): self
    {
        return new self($string);
    }
}
