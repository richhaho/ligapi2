<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\CustomFieldDto;
use App\Entity\CustomField;
use App\Entity\Data\CustomFieldType;
use App\Entity\Data\EntityType;
use App\Exceptions\Domain\InconsistentDataException;
use App\Services\CurrentUserProvider;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomFieldMapper
{
    use ValidationTrait;
    
    private ValidatorInterface $validator;
    private CurrentUserProvider $currentUserProvider;
    
    public function __construct(ValidatorInterface $validator, CurrentUserProvider $currentUserProvider)
    {
        $this->validator = $validator;
        $this->currentUserProvider = $currentUserProvider;
    }
    
    public function createCustomFieldFromDto(CustomFieldDto $customFieldDto): CustomField
    {
        $this->validate($customFieldDto);
        
        $company = $this->currentUserProvider->getAuthenticatedUser()->getCompany();
        
        $customField = new CustomField(
            $company,
            $customFieldDto->name,
            CustomFieldType::fromString($customFieldDto->type),
            EntityType::fromString($customFieldDto->entityType)
        );
        
        $customField->setOptions($customFieldDto->options);
        
        if ($customField->getType() === CustomFieldType::select()->getValue()) {
            if (!$customField->getOptions()) {
                throw InconsistentDataException::forDataIsMissing('custom field options');
            }
        }
        
        return $customField;
    }
    
    public function putCustomFieldFromDto(CustomFieldDto $customFieldDto, CustomField $customField): CustomField
    {
        $customFieldDto->id = $customField->getId();
        
        $this->validate($customFieldDto);
        
        $customField->setName($customFieldDto->name);
        $customField->setOptions($customFieldDto->options);
    
        if ($customField->getType() === CustomFieldType::select()->getValue()) {
            if (!$customField->getOptions()) {
                throw InconsistentDataException::forDataIsMissing('custom field options');
            }
        }
        
        return $customField;
    }
}
