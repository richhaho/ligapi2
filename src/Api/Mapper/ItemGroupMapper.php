<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\ItemGroupDto;
use App\Entity\Data\ItemGroupType;
use App\Entity\ItemGroup;
use App\Services\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ItemGroupMapper
{
    use ValidationTrait;
    
    private EntityManagerInterface $entityManager;
    private CurrentUserProvider $currentUserProvider;
    private ValidatorInterface $validator;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        CurrentUserProvider $currentUserProvider,
        ValidatorInterface $validator
    )
    {
    
        $this->entityManager = $entityManager;
        $this->currentUserProvider = $currentUserProvider;
        $this->validator = $validator;
    }
    
    public function createItemGroup(ItemGroupDto $itemGroupDto): ItemGroup
    {
        $this->validate($itemGroupDto);
        
        $currentUser = $this->currentUserProvider->getAuthenticatedUser();
        return new ItemGroup($itemGroupDto->name, ItemGroupType::fromString($itemGroupDto->itemGroupType), $currentUser->getCompany());
    }
    
    public function putItemGrouop(ItemGroupDto $itemGroupDto, ItemGroup $itemGroup): ItemGroup
    {
        $itemGroupDto->id = $itemGroup->getId();
        $this->validate($itemGroupDto);
        
        $itemGroup->setName($itemGroupDto->name);
        return $itemGroup;
    }
}
