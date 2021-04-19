<?php


namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class ChangeAction
{
    private const VALUES = ['create', 'update', 'delete', 'app'];
    
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
    
    public static function create(): self
    {
        return new self('create');
    }
    
    public static function update(): self
    {
        return new self('update');
    }
    
    public static function delete(): self
    {
        return new self('delete');
    }
    
    public static function app(): self
    {
        return new self('app');
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
