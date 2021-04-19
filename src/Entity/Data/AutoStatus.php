<?php


namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class AutoStatus
{
    private const VALUES = ['new', 'running', 'error', 'complete'];
    
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
    
    public static function new(): self
    {
        return new self('new');
    }
    
    public static function running(): self
    {
        return new self('running');
    }
    
    public static function error(): self
    {
        return new self('error');
    }
    
    public static function complete(): self
    {
        return new self('complete');
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
