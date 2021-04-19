<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\Company;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\PermissionGroup;
use App\Entity\Tool;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\PermissionGroupRepository;
use Doctrine\ORM\EntityManagerInterface;

class PermissionGroupService
{
    private array $permissionGroups;
    private PermissionGroupRepository $permissionGroupRepository;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        PermissionGroupRepository $permissionGroupRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->permissionGroupRepository = $permissionGroupRepository;
        $this->entityManager = $entityManager;
        $this->permissionGroups = [];
    }
    
    public function removePermissionGroupFromRelatedEntities(PermissionGroup $permissionGroup): void
    {
        /** @var Material $material */
        foreach ($permissionGroup->getMaterials() as $material) {
            $material->setPermissionGroup(null);
        }
    
        /** @var Tool $tool */
        foreach ($permissionGroup->getTools() as $tool) {
            $tool->setPermissionGroup(null);
        }
    
        /** @var Keyy $keyy */
        foreach ($permissionGroup->getKeyys() as $keyy) {
            $keyy->setPermissionGroup(null);
        }
    }
    
    public function getPermissionGroup(Company $company, ?string $name = null, ?string $id = null): ?PermissionGroup
    {
        if (!$id && !$name) {
            return null;
        }
        
        if ($id) {
            /** @var PermissionGroup $permissionGroup */
            foreach ($this->permissionGroups as $permissionGroup) {
                if ($permissionGroup->getId() === $id) {
                    return $permissionGroup;
                }
            }
            $permissionGroup = $this->permissionGroupRepository->find($id);
            if (!$permissionGroup) {
                throw MissingDataException::forEntityNotFound($id, PermissionGroup::class);
            }
            $this->permissionGroups[] = $permissionGroup;
            return $permissionGroup;
        }
        
        /** @var PermissionGroup $permissionGroup */
        foreach ($this->permissionGroups as $permissionGroup) {
            if ($permissionGroup->getName() === $name) {
                return $permissionGroup;
            }
        }
    
        $permissionGroup = $this->permissionGroupRepository->findByName($name);
        
        if ($permissionGroup) {
            return $permissionGroup;
        }
    
        $permissionGroup = new PermissionGroup($name, $company);
        $this->entityManager->persist($permissionGroup);
        
        $this->permissionGroups[] = $permissionGroup;
        
        return $permissionGroup;
    }
}
