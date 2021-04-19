<?php


namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class GridStateOwnerType
{
    private const VALUES = ['user', 'company', 'system'];
    
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
    
    public static function user(): self
    {
        return new self('user');
    }
    
    public static function company(): self
    {
        return new self('company');
    }
    
    public static function system(): self
    {
        return new self('system');
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
