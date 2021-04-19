<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\Company;
use App\Entity\Data\ItemGroupType;
use App\Entity\ItemGroup;
use App\Entity\Material;
use App\Entity\Tool;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\ItemGroupRepository;
use Doctrine\ORM\EntityManagerInterface;

class ItemGroupService
{
    private ItemGroupRepository $itemGroupRepository;
    private array $materialItemGroups;
    private array $toolItemGroups;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        ItemGroupRepository $itemGroupRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->itemGroupRepository = $itemGroupRepository;
        $this->materialItemGroups = [];
        $this->toolItemGroups = [];
        $this->entityManager = $entityManager;
    }
    
    public function removeItemGroupFromRelatedEntities(ItemGroup $itemGroup): void
    {
        /** @var Tool $tool */
        foreach ($itemGroup->getTools() as $tool) {
            $tool->setItemGroup(null);
        }
        /** @var Material $material */
        foreach ($itemGroup->getMaterials() as $material) {
            $material->setItemGroup(null);
        }
    }
    
    public function getMaterialGroup(Company $company, ?string $name = null, ?string $id = null): ?ItemGroup
    {
        $itemGroupType = ItemGroupType::material();
        
        if (!$id && !$name) {
            return null;
        }
        
        if ($id) {
            /** @var ItemGroup $itemGroup */
            foreach ($this->materialItemGroups as $itemGroup) {
                if ($itemGroup->getId() === $id) {
                    return $itemGroup;
                }
            }
            $itemGroup = $this->itemGroupRepository->find($id);
            if (!$itemGroup) {
                throw MissingDataException::forEntityNotFound($id, ItemGroup::class);
            }
            $this->materialItemGroups[] = $itemGroup;
            return $itemGroup;
        }
        
        /** @var ItemGroup $itemGroup */
        foreach ($this->materialItemGroups as $itemGroup) {
            if ($itemGroup->getName() === $name) {
                return $itemGroup;
            }
        }
    
        $itemGroup = $this->itemGroupRepository->findByNameAndType($name,$itemGroupType);
        
        if ($itemGroup) {
            return $itemGroup;
        }
        
        $itemGroup = new ItemGroup($name, $itemGroupType, $company);
        $this->entityManager->persist($itemGroup);
        
        $this->materialItemGroups[] = $itemGroup;
        
        return $itemGroup;
    }
    
    public function getToolGroup(Company $company, ?string $name = null, ?string $id = null): ?ItemGroup
    {
        $itemGroupType = ItemGroupType::tool();
        
        if (!$id && !$name) {
            return null;
        }
        
        if ($id) {
            /** @var ItemGroup $itemGroup */
            foreach ($this->toolItemGroups as $itemGroup) {
                if ($itemGroup->getId() === $id) {
                    return $itemGroup;
                }
            }
            $itemGroup = $this->itemGroupRepository->find($id);
            if (!$itemGroup) {
                throw MissingDataException::forEntityNotFound($id, ItemGroup::class);
            }
            $this->toolItemGroups[] = $itemGroup;
            return $itemGroup;
        }
        
        /** @var ItemGroup $itemGroup */
        foreach ($this->toolItemGroups as $itemGroup) {
            if ($itemGroup->getName() === $name) {
                return $itemGroup;
            }
        }
        
        $itemGroup = $this->itemGroupRepository->findByNameAndType($name,$itemGroupType);
        
        if ($itemGroup) {
            return $itemGroup;
        }
        
        $itemGroup = new ItemGroup($name, $itemGroupType, $company);
        $this->entityManager->persist($itemGroup);
        
        $this->toolItemGroups[] = $itemGroup;
        
        return $itemGroup;
    }
}
