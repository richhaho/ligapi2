<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers\Material;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\ItemGroupDto;
use App\Entity\Data\ItemGroupType;
use App\Services\Import\Transformers\TransformerInterface;

class PatchManyItemGroupNameTransformer implements TransformerInterface
{
    public function supports(string $transformer): bool
    {
        return $transformer === PatchManyItemGroupNameTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?ItemGroupDto
    {
        if (!isset($data[$property])) {
            return null;
        }
        $itemGroupDto = new ItemGroupDto();
        $itemGroupDto->itemGroupType = ItemGroupType::material()->getValue();
        $itemGroupDto->name = $data[$property];
        
        return $itemGroupDto;
    }
}
