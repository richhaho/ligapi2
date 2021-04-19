<?php


namespace App\Serializer;


use Money\Money;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MoneyNormalizer implements NormalizerInterface
{
    public function normalize($object, string $format = null, array $context = [])
    {
        return $object->getAmount() / 100;
    }
    
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Money;
    }
    
//    public function denormalize($data, string $type, string $format = null, array $context = [])
//    {
//        if (!isset($data['amount']) || !isset($data['currency'])) {
//        }
//        return new Money($data['amount'], new Currency($data['currency']));
//    }
//
//    public function supportsDenormalization($data, string $type, string $format = null)
//    {
//        return $type === Money::class;
//    }
}
