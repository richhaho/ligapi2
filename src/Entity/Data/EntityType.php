<?php


namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class EntityType
{
    private const VALUES = ['material', 'tool', 'keyy', 'order', 'consignment'];
    
    /**
     * @ORM\Column(type="string", length=20)
     */
    private string $value;
    
    private function __construct(string $value)
    {
        if (!in_array($value, self::VALUES)) {
            throw InvalidArgumentException::forInvalidElement($value, implode('/', self::VALUES));
        }
        $this->value = $value;
    }
    
    public static function material(): self
    {
        return new self('material');
    }
    
    public static function tool(): self
    {
        return new self('tool');
    }
    
    public static function keyy(): self
    {
        return new self('keyy');
    }
    
    public static function order(): self
    {
        return new self('order');
    }
    
    public static function consignment(): self
    {
        return new self('consignment');
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
