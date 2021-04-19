<?php


namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class LocationCategory
{
    private const VALUES = ['home', 'owner', 'main', 'additional', 'project'];
    
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
    
    public static function home(): self
    {
        return new self('home');
    }
    
    public static function owner(): self
    {
        return new self('owner');
    }
    
    public static function main(): self
    {
        return new self('main');
    }
    
    public static function additional(): self
    {
        return new self('additional');
    }
    
    public static function project(): self
    {
        return new self('project');
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
