<?php


namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class PaymentType
{
    private const VALUES = ['directDebit', 'creditCard', 'payPal', 'applePay'];
    
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private ?string $value = null;
    
    private function __construct(string $value)
    {
        if (!in_array($value, self::VALUES)) {
            throw InvalidArgumentException::forInvalidElement($value, implode('/', self::VALUES));
        }
        $this->value = $value;
    }
    
    public static function directDebit(): self
    {
        return new self('directDebit');
    }
    
    public static function creditCard(): self
    {
        return new self('creditCard');
    }
    
    public static function payPal(): self
    {
        return new self('payPal');
    }
    
    public static function applePay(): self
    {
        return new self('applePay');
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
