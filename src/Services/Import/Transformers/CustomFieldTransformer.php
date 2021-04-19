<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\BaseEntityDtoInterface;
use App\Api\Dto\DtoInterface;
use App\Entity\Data\CustomFieldType;
use App\Exceptions\Domain\InconsistentDataException;
use App\Services\CurrentUserProvider;
use App\Services\CustomFieldService;

class CustomFieldTransformer implements TransformerInterface
{
    private CustomFieldService $customFieldService;
    private CurrentUserProvider $currentUserProvider;
    
    public function __construct(
        CustomFieldService $customFieldService,
        CurrentUserProvider $currentUserProvider
    )
    {
        $this->customFieldService = $customFieldService;
        $this->currentUserProvider = $currentUserProvider;
    }
    
    public function supports(string $transformer): bool
    {
        return $transformer === CustomFieldTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?array
    {
        if (!$data[$title]) {
            return null;
        }
        
        $currentCustomFields = [];
        if (property_exists($dto, 'customFields') && $dto->customFields) {
            $currentCustomFields = $dto->customFields;
        }
        
        $isBoolean = str_contains($data[$title], '|boolean');
        
        if ($isBoolean) {
            $title = str_replace('|boolean', '', $title);
        }
    
        if (!$dto instanceof BaseEntityDtoInterface) {
            throw InconsistentDataException::forDtoMustImplementBaseEntityDto();
        }
        
        $customField = $this->customFieldService->getCustomField(
            $this->currentUserProvider->getCompany(),
            $title,
            $dto->getEntityType(),
            $isBoolean ? CustomFieldType::checkbox() : CustomFieldType::text()
        );
        
        if ($isBoolean) {
            $currentCustomFields[$customField->getId()] = !!$data[$title];
        } else {
            $currentCustomFields[$customField->getId()] = $data[$title];
        }
    
        return $currentCustomFields;
    }
}
