<?php

declare(strict_types=1);

namespace App\Api\Mapper;

use App\Api\Dto\BaseTool;
use App\Api\Dto\BatchUpdateDtoInterface;
use App\Api\Dto\CreateTool;
use App\Api\Dto\DtoInterface;
use App\Api\Dto\FileDto;
use App\Api\Dto\ManyDto;
use App\Api\Dto\PutTool;
use App\Api\Dto\ToolBatchUpdateDto;
use App\Api\Dto\ToolBatchUpdatesDto;
use App\Entity\Data\ChangeAction;
use App\Entity\OwnerChange;
use App\Entity\PermissionGroup;
use App\Entity\User;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\PermissionGroupRepository;
use App\Repository\ToolRepository;
use App\Repository\UserRepository;
use App\Services\Crawler\Downloader;
use App\Services\CurrentUserProvider;
use App\Services\FileService;
use App\Services\ItemGroupService;
use App\Services\ItemNumberService;
use App\Services\LocationService;
use App\Entity\Tool;
use App\Services\PermissionGroupService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ToolMapper implements MapperWithBatchUpdateInterface
{
    use ValidationTrait;
    
    private EntityManagerInterface $entityManager;
    private LocationService $locationService;
    private CurrentUserProvider $currentUserProvider;
    private EventDispatcherInterface $eventDispatcher;
    private ToolRepository $toolRepository;
    private ValidatorInterface $validator;
    private PermissionGroupRepository $permissionGroupRepository;
    private ItemGroupService $itemGroupService;
    private ItemNumberService $itemNumberService;
    private UserRepository $userRepository;
    private RequestContext $requestContext;
    private PermissionGroupService $permissionGroupService;
    private FileService $fileService;
    private Downloader $downloader;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        CurrentUserProvider $currentUserProvider,
        LocationService $locationService,
        EventDispatcherInterface $eventDispatcher,
        ToolRepository $toolRepository,
        ValidatorInterface $validator,
        PermissionGroupRepository $permissionGroupRepository,
        ItemGroupService $itemGroupService,
        ItemNumberService $itemNumberService,
        UserRepository $userRepository,
        RequestContext $requestContext,
        PermissionGroupService $permissionGroupService,
        FileService $fileService,
        Downloader $downloader
    )
    {
        $this->entityManager = $entityManager;
        $this->locationService = $locationService;
        $this->currentUserProvider = $currentUserProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->toolRepository = $toolRepository;
        $this->validator = $validator;
        $this->permissionGroupRepository = $permissionGroupRepository;
        $this->itemGroupService = $itemGroupService;
        $this->itemNumberService = $itemNumberService;
        $this->userRepository = $userRepository;
        $this->requestContext = $requestContext;
        $this->permissionGroupService = $permissionGroupService;
        $this->fileService = $fileService;
        $this->downloader = $downloader;
    }
    
    public function supports(string $dtoName): bool
    {
        return $dtoName === CreateTool::class || $dtoName === PutTool::class || $dtoName === ToolBatchUpdatesDto::class;
    }
    
    private function setToolData(BaseTool $baseTool, Tool $tool): Tool
    {
        $tool->setNote($baseTool->note);
        $tool->setPurchasingPrice($baseTool->purchasingPrice);
        $tool->setManufacturerNumber($baseTool->manufacturerNumber);
        $tool->setManufacturerName($baseTool->manufacturerName);
        $tool->setBarcode($baseTool->barcode);
        $tool->setBlecode($baseTool->blecode);
        $customFieldsWithValue = [];
        foreach ($baseTool->customFields as $index => $customField) {
            if ($customField) {
                $customFieldsWithValue[$index] = $customField;
            }
        }
        $tool->setCustomFields($customFieldsWithValue);
        $tool->setAltScannerIds($baseTool->altScannerIds);
        $tool->setIsBroken(!!$baseTool->isBroken);
    
        if ($baseTool->usableTill) {
            $tool->setUsableTill(new DateTimeImmutable($baseTool->usableTill));
        } else {
            $tool->setUsableTill(null);
        }
        
        if ($baseTool->purchasingDate) {
            $tool->setPurchasingDate(new DateTimeImmutable($baseTool->purchasingDate));
        } else {
            $tool->setPurchasingDate(null);
        }
    
        if ($baseTool->itemGroup && $baseTool->itemGroup->name) {
            $itemGroup = $this->itemGroupService->getToolGroup($tool->getCompany(), $baseTool->itemGroup->name);
            $tool->setItemGroup($itemGroup);
        } else if ($baseTool->itemGroup && $baseTool->itemGroup->id) {
            $itemGroup = $this->itemGroupService->getToolGroup($tool->getCompany(), null, $baseTool->itemGroup->id);
            $tool->setItemGroup($itemGroup);
        } else {
            $tool->setItemGroup(null);
        }
    
        if ($baseTool->permissionGroup) {
            $permissionGroup = $this->permissionGroupRepository->find($baseTool->permissionGroup->id);
            if (!$permissionGroup) {
                throw MissingDataException::forEntityNotFound($baseTool->permissionGroup->id, PermissionGroup::class);
            }
            $tool->setPermissionGroup($permissionGroup);
        } else {
            $tool->setPermissionGroup(null);
        }
        
        return $tool;
    }
    
    /**
     * @param CreateTool $dto
     */
    public function createEntityFromDto(object $dto, string $userId): Tool
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $this->validate($dto);
        
        $home = $this->locationService->mapStringToLocation($dto->home, $user->getCompany());
        
        if ($dto->owner && $dto->home !== $dto->owner) {
            $owner = $this->locationService->mapStringToLocation($dto->owner, $user->getCompany());
        } else {
            $owner = $home;
        }
    
        $dto->itemNumber = (string) ($this->itemNumberService->getNextItemNumber(Tool::class, $user->getCompany()));
        
        $tool = new Tool($dto->itemNumber, $dto->name ?? '?', $home, $owner, $user->getCompany());

        $tool = $this->setToolData($dto, $tool);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $tool));
        
        return $tool;
    }
    
    /**
     * @param PutTool $dto
     * @param Tool $entity
     */
    public function putEntityFromDto(DtoInterface $dto, object $entity): Tool
    {
        $dto->id = $entity->getId();
        $this->validate($dto);
        
        $entity->setName($dto->name ?? '?');
    
        $home = $this->locationService->mapStringToLocation($dto->home, $entity->getCompany());
        
        if ($dto->home !== $dto->owner) {
            $owner = $this->locationService->mapStringToLocation($dto->owner, $entity->getCompany());
        } else {
            $owner = $home;
        }
    
        if ($owner->getName() !== $entity->getOwner()) {
            $ownerChange = new OwnerChange($entity->getCompany(), $owner->getName(), $entity);
            $this->entityManager->persist($ownerChange);
        }
        
        $entity->setHome($home);
        $entity->setOwner($owner);
        
        $entity = $this->setToolData($dto, $entity);
    
        $entity->setUpdatedAt(new DateTimeImmutable());
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
        
        return $entity;
    }
    
    public function patchManyFromDto(ManyDto $patchManyDto)
    {
        foreach ($patchManyDto->ids as $id) {
            $tool = $this->toolRepository->find($id);
            if (!$tool) {
                throw MissingDataException::forEntityNotFound($id, Tool::class);
            }
//            $class = get_class($tool);
            foreach ($patchManyDto->query as $key => $value) {
//                $reflProperty = new ReflectionProperty($class, $key);
                // TODO: Check, if setter exists on object and make setter dynamic
                if ($key === 'isArchived') {
                    $tool->setIsArchived($value);
                }
                $tool->setUpdatedAt(new DateTimeImmutable());
                $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $tool));
            }
        }
    }
    
    /**
     * @param ToolBatchUpdateDto $dto
     * @param Tool $entity
     */
    public function patchEntityFromDto(DtoInterface $dto, object $entity): Tool
    {
        $company = $this->currentUserProvider->getCompany();
    
        if (isset($dto->name)) {
            $entity->setName($dto->name ?? '?');
        }
    
        if (isset($dto->itemGroup)) {
            $itemGroup = null;
            if ($dto->itemGroup) {
                $itemGroup = $this->itemGroupService->getToolGroup($company, $dto->itemGroup);
            }
            $entity->setItemGroup($itemGroup);
        }
        
        if (isset($dto->manufacturerName)) {
            $entity->setManufacturerName($dto->manufacturerName);
        }
        
        if (isset($dto->manufacturerNumber)) {
            $entity->setManufacturerNumber($dto->manufacturerNumber);
        }
        
        if (isset($dto->home)) {
            $home = $this->locationService->mapStringToLocation($dto->home, $entity->getCompany());
            $entity->setHome($home);
        }
        
        if (isset($dto->owner)) {
            $owner = $this->locationService->mapStringToLocation($dto->owner, $entity->getCompany());
            $entity->setOwner($owner);
        }
        
        if (isset($dto->usableTill)) {
            $usableTill = null;
            if ($dto->usableTill) {
                $usableTill = new DateTimeImmutable($dto->usableTill);
            }
            $entity->setusableTill($usableTill);
        }
        
        if (isset($dto->isBroken)) {
            $entity->setIsBroken(!!$dto->isBroken);
        }
        
        if (isset($dto->purchasingPrice)) {
            $entity->setPurchasingPrice((float) $dto->purchasingPrice);
        }
        
        if (isset($dto->barcode)) {
            $entity->setBarcode($dto->barcode);
        }
        
        if (isset($dto->blecode)) {
            $entity->setBlecode($dto->blecode);
        }
        
        if (isset($dto->purchasingDate)) {
            $purchasingDate = null;
            if ($dto->purchasingDate) {
                $purchasingDate = new DateTimeImmutable($dto->purchasingDate);
            }
            $entity->setPurchasingDate($purchasingDate);
        }
        
        if (isset($dto->note)) {
            $entity->setNote($dto->note);
        }
        
        if (isset($dto->customFields)) {
            $entity->setcustomFields($dto->customFields);
        }
    
        if (isset($dto->permissionGroup)) {
            if ($dto->permissionGroup) {
                $entity->setPermissionGroup(
                    $this->permissionGroupService->getPermissionGroup(
                        $company,
                        $dto->permissionGroup->name
                    )
                );
            } else {
                $entity->setPermissionGroup(null);
            }
        }
    
        if (isset($dto->customFields)) {
            $currentCustomFields = $entity->getCustomFields();
            foreach ($dto->customFields as $index => $customField) {
                $currentCustomFields[$index] = $customField;
            }
            $entity->setcustomFields($currentCustomFields);
        }
    
        if (isset($dto->profileImage)) {
            $currentProfileImage = $entity->getProfileImage();
            if ($currentProfileImage) {
                $fileDto = new FileDto();
                $fileDto->docType = 'profileImage';
                $fileDto->relativePath = $currentProfileImage;
                $fileDto->mimeType = 'img/jpeg';
                $fileDto->displayedName = 'Profilbild';
                $fileDto->size = 0;
                $fileDto->docType = 'image';
                $this->fileService->removeFile(get_class($entity), $entity->getId(), $fileDto);
            }
            if ($dto->profileImage) {
                $url = $dto->profileImage;
                $this->fileService->addFileToEntity($this->downloader->downloadCompanyUrl($url), $entity, 'profileImage', 'Profilbild');
            }
        }
        
        $entity->setUpdatedAt(new DateTimeImmutable());
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
        
        return $entity;
    }
    
    /**
     * @param CreateTool $dto
     */
    public function prepareImport(DtoInterface $dto, string $userId): ?CreateTool
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
    
        if ($dto->altScannerIds) {
            foreach ($dto->altScannerIds as $altScannerId) {
                $existingEntity = $this->toolRepository->findByAltScannerId($altScannerId);
                if ($existingEntity) {
                    return null;
                }
            }
        }
        return $dto;
    }
    
    /**
     * @param ToolBatchUpdatesDto $batchUpdateDto
     * @return Tool[]
     */
    public function batchUpdateFromDto(BatchUpdateDtoInterface $batchUpdateDto): iterable
    {
        $this->validate($batchUpdateDto);
        
        $updatedMaterials = [];
        foreach ($batchUpdateDto->toolBatchUpdates as $item) {
            $entity = $this->toolRepository->find($item->id);
            if (!$entity) {
                throw MissingDataException::forEntityNotFound($item->id, Tool::class);
            }
            $entity = $this->patchEntityFromDto($item, $entity);
            $updatedMaterials[] = $entity;
        }
        return $updatedMaterials;
    }
}
