<?php


namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class MaterialOrderType
{
    private const VALUES = ['webshop', 'email', 'pdf', 'manual', 'ugl'];
    
    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private ?string $value = null;
    
    private function __construct(string $value)
    {
        if (!in_array($value, self::VALUES)) {
            throw InvalidArgumentException::forInvalidElement($value, implode('/', self::VALUES));
        }
        $this->value = $value;
    }
    
    public static function webshop(): self
    {
        return new self('webshop');
    }
    
    public static function email(): self
    {
        return new self('email');
    }
    
    public static function pdf(): self
    {
        return new self('pdf');
    }
    
    public static function manual(): self
    {
        return new self('manual');
    }
    
    public static function ugl(): self
    {
        return new self('ugl');
    }
    
    public function getValue(): ?string
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
