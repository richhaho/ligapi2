<?php

declare(strict_types=1);

namespace App\Api\Mapper;

use App\Api\Dto\BaseKeyy;
use App\Api\Dto\BatchUpdateDtoInterface;
use App\Api\Dto\CreateKeyy;
use App\Api\Dto\DtoInterface;
use App\Api\Dto\FileDto;
use App\Api\Dto\KeyyBatchUpdateDto;
use App\Api\Dto\KeyyBatchUpdatesDto;
use App\Api\Dto\PutKeyy;
use App\Entity\Data\ChangeAction;
use App\Entity\OwnerChange;
use App\Entity\PermissionGroup;
use App\Entity\User;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\KeyyRepository;
use App\Repository\PermissionGroupRepository;
use App\Repository\UserRepository;
use App\Services\Crawler\Downloader;
use App\Services\CurrentUserProvider;
use App\Services\FileService;
use App\Services\ItemNumberService;
use App\Services\LocationService;
use App\Entity\Keyy;
use App\Services\PermissionGroupService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class KeyyMapper implements MapperWithBatchUpdateInterface
{
    use ValidationTrait;
    
    private EntityManagerInterface $entityManager;
    private LocationService $locationService;
    private CurrentUserProvider $currentUserProvider;
    private EventDispatcherInterface $eventDispatcher;
    private KeyyRepository $keyyRepository;
    private ValidatorInterface $validator;
    private PermissionGroupRepository $permissionGroupRepository;
    private ItemNumberService $itemNumberService;
    private UserRepository $userRepository;
    private RequestContext $requestContext;
    private PermissionGroupService $permissionGroupService;
    private FileService $fileService;
    private Downloader $downloader;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        CurrentUserProvider $currentUserProvider,
        LocationService $LocationService,
        EventDispatcherInterface $eventDispatcher,
        KeyyRepository $keyyRepository,
        ValidatorInterface $validator,
        PermissionGroupRepository $permissionGroupRepository,
        ItemNumberService $itemNumberService,
        UserRepository $userRepository,
        RequestContext $requestContext,
        PermissionGroupService $permissionGroupService,
        FileService $fileService,
        Downloader $downloader
    )
    {
        $this->entityManager = $entityManager;
        $this->locationService = $LocationService;
        $this->currentUserProvider = $currentUserProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->keyyRepository = $keyyRepository;
        $this->validator = $validator;
        $this->permissionGroupRepository = $permissionGroupRepository;
        $this->itemNumberService = $itemNumberService;
        $this->userRepository = $userRepository;
        $this->requestContext = $requestContext;
        $this->permissionGroupService = $permissionGroupService;
        $this->fileService = $fileService;
        $this->downloader = $downloader;
    }
    
    public function supports(string $dtoName): bool
    {
        return $dtoName === CreateKeyy::class || $dtoName === KeyyBatchUpdatesDto::class;
    }
    
    private function setKeyyData(BaseKeyy $baseKeyy, Keyy $keyy): Keyy
    {
        $keyy->setAmount($baseKeyy->amount);
        $keyy->assignName($baseKeyy->name ?? '?');
        $keyy->setAddress($baseKeyy->address);
        $keyy->annotate($baseKeyy->note);
        $customFieldsWithValue = [];
        foreach ($baseKeyy->customFields as $index => $customField) {
            if ($customField) {
                $customFieldsWithValue[$index] = $customField;
            }
        }
        $keyy->setCustomFields($customFieldsWithValue);
        $keyy->setAltScannerIds($baseKeyy->altScannerIds);
    
        if ($baseKeyy->permissionGroup) {
            $permissionGroup = $this->permissionGroupRepository->find($baseKeyy->permissionGroup->id);
            if (!$permissionGroup) {
                throw MissingDataException::forEntityNotFound($baseKeyy->permissionGroup->id, PermissionGroup::class);
            }
            $keyy->setPermissionGroup($permissionGroup);
        } else {
            $keyy->setPermissionGroup(null);
        }
        
        return $keyy;
    }
    
    /**
     * @param CreateKeyy $dto
     */
    public function createEntityFromDto(object $dto, string $userId): Keyy
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
        
        $dto->itemNumber = (string) ($this->itemNumberService->getNextItemNumber(Keyy::class, $user->getCompany()));
        
        $keyy = new Keyy($dto->itemNumber, $dto->name ?? '?', $home, $owner, $user->getCompany());

        $keyy = $this->setKeyyData($dto, $keyy);
        
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $keyy));
        
        return $keyy;
    }
    
    /**
     * @param PutKeyy $dto
     * @param Keyy $entity
     */
    public function putEntityFromDto(object $dto, object $entity): Keyy
    {
        $dto->id = $entity->getId();
    
        $this->validate($dto);
        
        $home = $this->locationService->mapStringToLocation($dto->home, $entity->getCompany());
        
        if ($dto->home !== $dto->owner) {
            $owner = $this->locationService->mapStringToLocation($dto->owner, $entity->getCompany());
        } else {
            $owner = $home;
        }
    
        if ($owner->getName() !== $entity->getOwner()) {
            $ownerChange = new OwnerChange($entity->getCompany(), $owner->getName(), null, $entity);
            $this->entityManager->persist($ownerChange);
        }
        
        $entity->setHome($home);
        $entity->setOwner($owner);
        
        $entity = $this->setKeyyData($dto, $entity);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
        
        return $entity;
    }
    
    /**
     * @param CreateKeyy $dto
     */
    public function prepareImport(DtoInterface $dto, string $userId): ?CreateKeyy
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        if ($dto->altScannerIds) {
            foreach ($dto->altScannerIds as $altScannerId) {
                $existingEntity = $this->keyyRepository->findByAltScannerId($altScannerId);
                if ($existingEntity) {
                    return null;
                }
            }
        }
        return $dto;
    }
    
    /**
     * @param KeyyBatchUpdateDto $dto
     * @param Keyy $entity
     */
    public function patchEntityFromDto(DtoInterface $dto, object $entity): Keyy
    {
        $company = $this->currentUserProvider->getCompany();
    
        if (isset($dto->name)) {
            $entity->setName($dto->name ?? '?');
        }
    
        if (isset($dto->amount)) {
            if ($dto->amount) {
                $entity->setAmount((int) $dto->amount);
            } else {
                $entity->setAmount(null);
            }
        }
    
        if (isset($dto->permissionGroup)) {
            if ($dto->permissionGroup) {
                $entity->setPermissionGroup(
                    $this->permissionGroupService->getPermissionGroup(
                        $company,
                        $dto->permissionGroup->name)
                );
            } else {
                $entity->setPermissionGroup(null);
            }
        }
    
        if (isset($dto->note)) {
            $entity->annotate($dto->note);
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
    
        if (isset($dto->home)) {
            $entity->setHome($this->locationService->mapStringToLocation($dto->home, $entity->getCompany()));
        }
        
        if (isset($dto->owner)) {
            $entity->setOwner($this->locationService->mapStringToLocation($dto->owner, $entity->getCompany()));
        }
        
        if (isset($dto->address)) {
            $entity->setAddress($dto->address);
        }
    
        $entity->setUpdatedAt(new DateTimeImmutable());
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
        
        return $entity;
    }
    
    /**
     * @param KeyyBatchUpdatesDto $batchUpdateDto
     * @return Keyy[]
     */
    public function batchUpdateFromDto(BatchUpdateDtoInterface $batchUpdateDto): iterable
    {
        $this->validate($batchUpdateDto);
        
        $updatedKeyys = [];
        foreach ($batchUpdateDto->keyyBatchUpdates as $item) {
            $keyy = $this->keyyRepository->find($item->id);
            if (!$keyy) {
                throw MissingDataException::forEntityNotFound($item->id, Keyy::class);
            }
            $keyy = $this->patchEntityFromDto($item, $keyy);
            $updatedKeyys[] = $keyy;
        }
        return $updatedKeyys;
    }
}
