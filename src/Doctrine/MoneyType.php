<?php


namespace App\Doctrine;


use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Money\Money;

class MoneyType extends Type
{
    const MONEY = 'money';
    
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'VARCHAR(255) COMMENT \'(DC2Type:money)\'';
    }
    
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return Money::EUR($value);
    }
    
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }
        if (!$value instanceof Money) {
            throw InvalidArgumentException::forUnsupportedMoney($value);
        }
        return $value->getAmount();
    }
    
    public function getName()
    {
        return self::MONEY;
    }
}
