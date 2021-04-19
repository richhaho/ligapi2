<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers\Tool;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\ItemGroupDto;
use App\Entity\Data\ItemGroupType;
use App\Services\Import\Transformers\TransformerInterface;

class ItemGroupTransformer implements TransformerInterface
{
    public function supports(string $transformer): bool
    {
        return $transformer === ItemGroupTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?ItemGroupDto
    {
        $itemGroupDto = new ItemGroupDto();
        $itemGroupDto->itemGroupType = ItemGroupType::tool()->getValue();
        $itemGroupDto->name = $data[$title];
        
        return $itemGroupDto;
    }
}
