<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\CreateUser;
use App\Api\Dto\DtoInterface;
use App\Api\Dto\MaterialLocationDto;
use App\Entity\Data\LocationCategory;
use App\Exceptions\Domain\InvalidArgumentException;

class LocationCategoryTransformer implements TransformerInterface
{
    public function supports(string $transformer): bool
    {
        return $transformer === LocationCategoryTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?string
    {
        if (!$dto instanceof MaterialLocationDto) {
            return null;
        }
        
        switch ($data[$title]) {
            case 'main':
                return LocationCategory::main()->getValue();
            case 'personal':
            case 'additional':
                return LocationCategory::additional()->getValue();
            case 'home':
                return LocationCategory::home()->getValue();
            case 'owner':
                return LocationCategory::owner()->getValue();
        }
        
        throw InvalidArgumentException::forUnsupportedLocationType('Import', $data[$property]);
    }
}
