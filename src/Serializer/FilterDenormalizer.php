<?php

declare(strict_types=1);


namespace App\Serializer;


use App\Api\Dto\FilterDto;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class FilterDenormalizer implements DenormalizerInterface
{
    
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $res = [];
        foreach ($data as $index => $item) {
            $filterDto = new FilterDto();
            $filterDto->type = $item['type'] ?? '';
            $filterDto->filter = $item['filter'] ?? '';
            $filterDto->filterType = $item['filterType'] ?? '';
            $filterDto->values = $item['values'] ?? [];
            $res[$index] = $filterDto;
        }
        return $res;
    }
    
    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === FilterDto::class . '[]';
    }
}
