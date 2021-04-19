<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers\Material;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\MaterialLocationDto;
use App\Entity\MaterialLocation;
use App\Services\Import\CreateDtoFromRequestService;
use App\Services\Import\Transformers\TransformerInterface;

class MaterialLocationTransformer implements TransformerInterface
{
    private CreateDtoFromRequestService $createDtoFromRequestService;
    
    public function __construct(CreateDtoFromRequestService $createDtoFromRequestService)
    {
        $this->createDtoFromRequestService = $createDtoFromRequestService;
    }
    
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === MaterialLocationTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): array
    {
        if (($data['materialLocations'] ?? "") === "") {
            return [];
        }
        
        return $this->createDtoFromRequestService->patchEntities(
            $data['materialLocations'],
            MaterialLocation::class,
            MaterialLocationDto::class
        );
    }
}
