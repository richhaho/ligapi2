<?php

declare(strict_types=1);


namespace App\Entity\Data;


use App\Exceptions\Domain\InvalidArgumentException;

class PdfAlignment
{
    private const VALUES = ['left', 'center', 'right'];
    
    private string $value;
    
    private function __construct(string $value)
    {
        if (!in_array($value, self::VALUES)) {
            throw InvalidArgumentException::forInvalidElement($value, implode('/', self::VALUES));
        }
        $this->value = $value;
    }
    
    public static function left(): self
    {
        return new self('left');
    }
    
    public static function center(): self
    {
        return new self('center');
    }
    
    public static function right(): self
    {
        return new self('right');
    }
    
    public function getValue(): string
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
