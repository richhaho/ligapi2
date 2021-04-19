<?php


namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class CustomFieldType
{
    private const VALUES = ['text', 'checkbox', 'select', 'float'];
    
    /**
     * @ORM\Column(type="string", length=10)
     */
    private string $value;
    
    private function __construct(string $value)
    {
        if (!in_array($value, self::VALUES)) {
            throw InvalidArgumentException::forInvalidElement($value, implode('/', self::VALUES));
        }
        $this->value = $value;
    }
    
    public static function text(): self
    {
        return new self('text');
    }
    
    public static function checkbox(): self
    {
        return new self('checkbox');
    }
    
    public static function select(): self
    {
        return new self('select');
    }
    
    public static function float(): self
    {
        return new self('float');
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
    
    public function __toString(): string
    {
        return $this->getValue();
    }
    
    public static function fromString($string): self
    {
        return new self($string);
    }
}
