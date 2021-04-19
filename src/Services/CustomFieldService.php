<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\Company;
use App\Entity\CustomField;
use App\Entity\Data\CustomFieldType;
use App\Entity\Data\EntityType;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\Tool;
use App\Repository\CustomFieldRepository;
use App\Repository\KeyyRepository;
use App\Repository\MaterialRepository;
use App\Repository\ToolRepository;
use Doctrine\ORM\EntityManagerInterface;

class CustomFieldService
{
    private array $customFields;
    private MaterialRepository $materialRepository;
    private ToolRepository $toolRepository;
    private KeyyRepository $keyyRepository;
    private CustomFieldRepository $customFieldRepository;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        MaterialRepository $materialRepository,
        CustomFieldRepository $customFieldRepository,
        ToolRepository $toolRepository,
        KeyyRepository $keyyRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->materialRepository = $materialRepository;
        $this->toolRepository = $toolRepository;
        $this->keyyRepository = $keyyRepository;
        $this->customFields = [];
        $this->customFieldRepository = $customFieldRepository;
        $this->entityManager = $entityManager;
    }
    
    public function removeCustomField(CustomField $customField): void
    {
        $materials = $this->materialRepository->getMaterialsWithCustomFieldSet($customField);
    
        /** @var Material $material */
        foreach ($materials as $material) {
            $customFields = null;
            foreach ($material->getCustomFields() as $index => $customF) {
                if ($index !== $customField->getId()) {
                    $customFields[$index] = $customF;
                }
            }
            $material->setCustomFields($customFields);
        }
        
        $tools = $this->toolRepository->getToolsWithCustomFieldSet($customField);
    
        /** @var Tool $tool */
        foreach ($tools as $tool) {
            $customFields = null;
            foreach ($tool->getCustomFields() as $index => $customF) {
                if ($index !== $customField->getId()) {
                    $customFields[$index] = $customF;
                }
            }
            $tool->setCustomFields($customFields);
        }
        
        $keyys = $this->keyyRepository->getKeyysWithCustomFieldSet($customField);
    
        /** @var Keyy $keyy */
        foreach ($keyys as $keyy) {
            $customFields = null;
            foreach ($keyy->getCustomFields() as $index => $customF) {
                if ($index !== $customField->getId()) {
                    $customFields[$index] = $customF;
                }
            }
            $keyy->setCustomFields($customFields);
        }
    }
    
    public function getCustomField(Company $company, string $name, EntityType $entityType, ?CustomFieldType $customFieldType = null): ?CustomField
    {
        $isBoolean = str_contains($name, '|boolean');
        $name = str_replace('|boolean', '', $name);
        
        /** @var CustomField $customField */
        foreach ($this->customFields as $customField) {
            if ($customField->getName() === $name && $customField->getEntityType() === $entityType->getValue()) {
                return $customField;
            }
        }
        
        $customField = $this->customFieldRepository->findByNameAndEntityType($name, $entityType);
        
        if ($customField) {
            return $customField;
        }
        
        if ($isBoolean) {
            $customFieldType = CustomFieldType::checkbox();
        }
        
        $customField = new CustomField( $company, $name, $customFieldType ?? CustomFieldType::text(), $entityType);
        $this->entityManager->persist($customField);
        
        $this->customFields[] = $customField;
        
        return $customField;
    }
}
