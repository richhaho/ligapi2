<?php


namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class OrderStatus
{
    private const VALUES = ['available', 'toOrder', 'onItsWay'];
    
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
    
    public static function available(): self
    {
        return new self('available');
    }
    
    public static function toOrder(): self
    {
        return new self('toOrder');
    }
    
    public static function onItsWay(): self
    {
        return new self('onItsWay');
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
