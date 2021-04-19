<?php


namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class PaymentCycleType
{
    private const VALUES = ['monthly', 'yearly', 'twoYearly'];
    
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
    
    public static function monthly(): self
    {
        return new self('monthly');
    }
    
    public static function yearly(): self
    {
        return new self('yearly');
    }
    
    public static function twoYearly(): self
    {
        return new self('twoYearly');
    }
    
    public function getValue(): ?string
    {
        return $this->value;
    }
    
    public function __toString(): ?string
    {
        return $this->getValue();
    }
    
    public static function fromString($string): self
    {
        return new self($string);
    }
}
