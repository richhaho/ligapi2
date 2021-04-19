<?php

declare(strict_types=1);


namespace App\Services\Import;


use App\Api\Dto\BaseEntityDtoInterface;
use App\Api\Dto\CreateKeyy;
use App\Api\Dto\CreateMaterialDto;
use App\Api\Dto\CreateTool;
use App\Api\Dto\DtoInterface;
use App\Exceptions\Domain\InconsistentDataException;
use App\Services\CurrentUserProvider;
use App\Services\CustomFieldService;
use App\Services\Import\Transformers\TransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class DtoService
{
    private PropertyAccessorInterface $propertyAccessor;
    private iterable $transformers;
    private array $importMappings;
    private CustomFieldService $customFieldService;
    private CurrentUserProvider $currentUserProvider;
    
    public function __construct(
        array $importMappings,
        PropertyAccessorInterface $propertyAccessor,
        iterable $transformers,
        CustomFieldService $customFieldService,
        CurrentUserProvider $currentUserProvider
    )
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->transformers = $transformers;
        $this->importMappings = $importMappings;
        $this->customFieldService = $customFieldService;
        $this->currentUserProvider = $currentUserProvider;
    }
    
    private function mapDataLineToDto(DtoInterface $dto, string $originalPropertyTitle, array $dataLine): DtoInterface
    {
        $company = $this->currentUserProvider->getCompany();
        $value = $dataLine[$originalPropertyTitle];
        $matchedProperty = null;
        
        // See, if there is an import mapping for the propertyName
        foreach ($this->importMappings as $importMapping) {
            if ($importMapping['title'] === $originalPropertyTitle) {
                $matchedProperty = $importMapping['property'];
                // Transform value if necessary;
                if ($importMapping['transformer']) {
                    /** @var TransformerInterface $transformer */
                    foreach ($this->transformers as $transformer) {
                        if ($transformer->supports($importMapping['transformer'])) {
                            $value = $transformer->transform($dataLine, $importMapping['property'], $dto, $originalPropertyTitle);
                        }
                    }
                }
            }
        }
        
        if ($matchedProperty && ($value || $value === 0|| $value === 0.0 || $value === '')) {
            $this->propertyAccessor->setValue($dto, $matchedProperty, $value);
        } else if (
            !$matchedProperty &&
            $value &&
            in_array(get_class($dto), [CreateMaterialDto::class, CreateTool::class, CreateKeyy::class])
        ) {
            if (!$dto instanceof BaseEntityDtoInterface) {
                throw InconsistentDataException::forDtoMustImplementBaseEntityDto();
            }
            $customFieldName = $originalPropertyTitle;
            $customField = $this->customFieldService->getCustomField($company, $customFieldName, $dto->getEntityType());
            if (!$dto->customFields) {
                $dto->customFields = [];
            }
            $dto->customFields[$customField->getId()] = $value;
        }
        
        return $dto;
    }
    
    public function createDtoFromArray(array $data, string $dtoName): DtoInterface
    {
        $dto = new $dtoName;
    
        foreach ($data as $index => $value) {
            if (!$value === null) {
                continue;
            }
            $dto = $this->mapDataLineToDto($dto, $index,$data);
        }
    
        return $dto;
    }
}
