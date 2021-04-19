<?php


namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class PdfSpecificationType
{
    private const VALUES = ['label', 'order', 'consignment'];
    
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
    
    public static function label(): self
    {
        return new self('label');
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
    
    public function getFileName(): string
    {
        switch ($this->getValue()) {
            case 'label':
                return 'etikett';
            case 'order':
                return 'bestellung';
            case 'consignment':
                return 'lieferung';
        }
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
