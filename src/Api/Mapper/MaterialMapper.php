<?php

declare(strict_types=1);

namespace App\Api\Mapper;

use App\Api\Dto\BaseMaterial;
use App\Api\Dto\BatchUpdateDtoInterface;
use App\Api\Dto\CreateMaterialDto;
use App\Api\Dto\DtoInterface;
use App\Api\Dto\AutoMaterialDto;
use App\Api\Dto\FileDto;
use App\Api\Dto\ManyDto;
use App\Api\Dto\MaterialBatchUpdateDto;
use App\Api\Dto\MaterialBatchUpdatesDto;
use App\Api\Dto\MaterialLocationDto;
use App\Api\Dto\OrderSourceDto;
use App\Api\Dto\PutMaterialDto;
use App\Api\Dto\SupplierDto;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\AutoStatus;
use App\Entity\Data\OrderStatus;
use App\Entity\Material;
use App\Entity\MaterialLocation;
use App\Entity\OrderSource;
use App\Entity\StockChange;
use App\Entity\Supplier;
use App\Entity\User;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\MaterialLocationRepository;
use App\Repository\MaterialRepository;
use App\Repository\OrderSourceRepository;
use App\Repository\SupplierRepository;
use App\Repository\UserRepository;
use App\Services\Crawler\Downloader;
use App\Services\CurrentUserProvider;
use App\Services\FileService;
use App\Services\ItemGroupService;
use App\Services\ItemNumberService;
use App\Services\LocationService;
use App\Services\MaterialLocationService;
use App\Services\OrderSourceService;
use App\Services\PermissionGroupService;
use App\Services\SupplierService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Money\Money;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MaterialMapper implements MapperWithBatchUpdateInterface
{
    use ValidationTrait;
    
    private EntityManagerInterface $entityManager;
    private LocationService $locationService;
    private MaterialLocationService $materialLocationService;
    private CurrentUserProvider $currentUserProvider;
    private OrderSourceService $orderSourceService;
    private EventDispatcherInterface $eventDispatcher;
    private MaterialRepository $materialRepository;
    private ValidatorInterface $validator;
    private SupplierRepository $supplierRepository;
    private FileService $fileService;
    private MaterialLocationMapper $materialLocationMapper;
    private OrderSourceMapper $orderSourceMapper;
    private MaterialLocationRepository $materialLocationRepository;
    private OrderSourceRepository $orderSourceRepository;
    private ItemNumberService $itemNumberService;
    private UserRepository $userRepository;
    private ItemGroupService $itemGroupService;
    private PermissionGroupService $permissionGroupService;
    private Downloader $downloader;
    private RequestContext $requestContext;
    private SupplierService $supplierService;
    private string $apiUrl;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        CurrentUserProvider $currentUserProvider,
        LocationService $locationService,
        MaterialLocationService $materialLocationService,
        OrderSourceService $orderSourceService,
        EventDispatcherInterface $eventDispatcher,
        MaterialRepository $materialRepository,
        ValidatorInterface $validator,
        SupplierRepository $supplierRepository,
        FileService $fileService,
        MaterialLocationMapper $materialLocationMapper,
        OrderSourceMapper $orderSourceMapper,
        MaterialLocationRepository $materialLocationRepository,
        OrderSourceRepository $orderSourceRepository,
        ItemNumberService $itemNumberService,
        UserRepository $userRepository,
        ItemGroupService $itemGroupService,
        PermissionGroupService $permissionGroupService,
        Downloader $downloader,
        SupplierService $supplierService,
        RequestContext $requestContext,
        string $apiUrl
    )
    {
        $this->entityManager = $entityManager;
        $this->locationService = $locationService;
        $this->materialLocationService = $materialLocationService;
        $this->currentUserProvider = $currentUserProvider;
        $this->orderSourceService = $orderSourceService;
        $this->eventDispatcher = $eventDispatcher;
        $this->materialRepository = $materialRepository;
        $this->validator = $validator;
        $this->supplierRepository = $supplierRepository;
        $this->fileService = $fileService;
        $this->materialLocationMapper = $materialLocationMapper;
        $this->orderSourceMapper = $orderSourceMapper;
        $this->materialLocationRepository = $materialLocationRepository;
        $this->orderSourceRepository = $orderSourceRepository;
        $this->itemNumberService = $itemNumberService;
        $this->userRepository = $userRepository;
        $this->itemGroupService = $itemGroupService;
        $this->permissionGroupService = $permissionGroupService;
        $this->downloader = $downloader;
        $this->requestContext = $requestContext;
        $this->supplierService = $supplierService;
        $this->apiUrl = $apiUrl;
    }
    
    public function supports(string $dtoName): bool
    {
        return $dtoName === CreateMaterialDto::class || $dtoName === PutMaterialDto::class || $dtoName === MaterialBatchUpdatesDto::class;
    }
    
    /**
     * @param Material $material
     * @param BaseMaterial $baseMaterial
     * @return Material
     */
    private function setData(object $material, object $baseMaterial): Material
    {
        $user = $this->currentUserProvider->getAuthenticatedUser();
        
        $material->annotate($baseMaterial->note);
        $material->setManufacturerNumber($baseMaterial->manufacturerNumber);
        $material->setManufacturerName($baseMaterial->manufacturerName);
        $material->setBarcode($baseMaterial->barcode);
        $material->setUnit($baseMaterial->unit);
        $material->setUnitAlt($baseMaterial->unitAlt);
        
        if ($baseMaterial->usableTill) {
            $material->setUsableTill(new DateTimeImmutable($baseMaterial->usableTill));
        } else {
            $material->setUsableTill(null);
        }
        $material->setPermanentInventory($baseMaterial->permanentInventory);
        $material->setUnitConversion($baseMaterial->unitConversion);
        $material->setAutoSearchTerm($baseMaterial->autoSearchTerm);
        $material->setOrderStatusNote($baseMaterial->orderStatusNote);
        $customFieldsWithValue = [];
        foreach ($baseMaterial->customFields as $index => $customField) {
            if ($customField) {
                $customFieldsWithValue[$index] = $customField;
            }
        }
        $material->setCustomFields($customFieldsWithValue);
        $material->setOrderAmount($baseMaterial->orderAmount);
    
        if ($baseMaterial->orderStatus && $baseMaterial->orderStatus !== $material->getOrderStatus()) {
            $material->updateOrderStatus(OrderStatus::fromString($baseMaterial->orderStatus), $user);
        }
        
        if ($baseMaterial->autoSearchTerm && !$material->getAutoStatus()) {
            $material->setAutoStatus(AutoStatus::new());
        }
    
        if ($baseMaterial->sellingPrice) {
            $material->setSellingPrice(Money::EUR($baseMaterial->sellingPrice * 100));
        } else {
            $material->setSellingPrice(null);
        }
    
        if ($baseMaterial->itemGroup && $baseMaterial->itemGroup->name) {
            $itemGroup = $this->itemGroupService->getMaterialGroup($material->getCompany(), $baseMaterial->itemGroup->name);
            $material->setItemGroup($itemGroup);
        } else if ($baseMaterial->itemGroup && $baseMaterial->itemGroup->id) {
            $itemGroup = $this->itemGroupService->getMaterialGroup($material->getCompany(), null, $baseMaterial->itemGroup->id);
            $material->setItemGroup($itemGroup);
        } else {
            $material->setItemGroup(null);
        }
    
        if ($baseMaterial->permissionGroup) {
            $material->setPermissionGroup($this->permissionGroupService->getPermissionGroup(
                $user->getCompany(),
                $baseMaterial->permissionGroup->name,
                $baseMaterial->permissionGroup->id)
            );
        } else {
            $material->setPermissionGroup(null);
        }
    
        foreach ($baseMaterial->materialLocations as $materialLocationDto) {
            if ($materialLocationDto->id) {
                $materialLocation = $this->materialLocationRepository->find($materialLocationDto->id);
                if (!$materialLocation) {
                    throw MissingDataException::forEntityNotFound($materialLocationDto->id, MaterialLocation::class);
                }
                $this->materialLocationMapper->putEntityFromDto($materialLocationDto, $materialLocation);
            } else {
                $this->materialLocationService->addLocationToMaterial($materialLocationDto, $material);
            }
        }
    
        foreach ($baseMaterial->orderSources as $orderSourceDto) {
            if ($orderSourceDto->id) {
                $orderSource = $this->orderSourceRepository->find($orderSourceDto->id);
                if (!$orderSource) {
                    throw MissingDataException::forEntityNotFound($orderSourceDto->id, OrderSource::class);
                }
                $this->orderSourceMapper->putEntityFromDto($orderSourceDto, $orderSource);
            } else {
                $this->orderSourceService->addOrderSourceToMaterial($orderSourceDto, $material);
            }
        }
    
        return $material;
    }
    
    /**
     * @param CreateMaterialDto $dto
     */
    public function createEntityFromDto(object $dto, string $userId): Material
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $this->validate($dto);
        
        $company = $this->currentUserProvider->getCompany();
        
        if (!$dto->itemNumber) {
            $dto->itemNumber = (string) ($this->itemNumberService->getNextItemNumber(Material::class, $user->getCompany()));
        }
        
        $material = new Material($dto->itemNumber, $dto->name ?? '?', $user->getCompany());

        $material = $this->setData($material, $dto);
        
        if ($dto->autoSupplier && $dto->autoSupplier->id) {
            /** @var Supplier $autoSupplier */
            $autoSupplier = $this->supplierService->getSupplier($company, null, $dto->autoSupplier->id);
            if (!$autoSupplier) {
                throw MissingDataException::forEntityNotFound($dto->autoSupplier->id, Supplier::class);
            }
            $material->setAutoSupplier($autoSupplier);
            if (!$dto->autoSearchTerm) {
                throw MissingDataException::forMissingData('material search term');
            }
        }
        
        if ($dto->autoSearchTerm && !$dto->autoSupplier->id) {
            throw MissingDataException::forMissingData('auto supplier id');
        } else if ($dto->autoSearchTerm) {
            $material->setAutoStatus(AutoStatus::new());
            $material->setAutoSearchTerm($dto->autoSearchTerm);
        }
    
        if ($dto->profileImageUrl) {
            $this->fileService->addFileToEntity($this->downloader->downloadCompanyUrl($dto->profileImageUrl), $material, 'profileImage', 'Profilbild');
        }
        
        $material->setAltScannerIds($dto->altScannerIds);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $material));
        
        return $material;
    }
    
    /**
     * @param PutMaterialDto $dto
     * @param Material $entity
     * @return Material
     */
    public function putEntityFromDto(DtoInterface $dto, object $entity): Material
    {
        $dto->id = $entity->getId();
        $this->validate($dto);
        
        $entity->setName($dto->name ?? '?');
//
//        if ($dto->orderStatus && $dto->orderStatus !== $entity->getOrderStatus()) {
//            $user = $this->currentUserProvider->getAuthenticatedUser();
//            $entity->updateOrderStatus(OrderStatus::fromString($dto->orderStatus), $user);
//        }
    
        $entity = $this->setData($entity, $dto);
        
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
        
        return $entity;
    }
    
    public function putMaterialFromAutoMaterialDto(AutoMaterialDto $autoMaterialDto, Material $material, Supplier $supplier): Material
    {
        $existingOrderSource = $this->orderSourceService->materialAlreadyHasOrderSourceForSupplier($material, $supplier);

        if ($existingOrderSource) {
            foreach ($autoMaterialDto->orderSources as $orderSourceDto) {
                if ($orderSourceDto->supplier->id === $supplier->getId()) {
                    $existingOrderSource->setOrderNumber($orderSourceDto->orderNumber);
                    $existingOrderSource->setPrice($orderSourceDto->price);
                    $existingOrderSource->setAmountPerPurchaseUnit($orderSourceDto->amountPerPurchaseUnit);
                    $existingOrderSource->setLastPriceUpdate(new DateTimeImmutable());
                }
            }
        } else {
            $orderSource = new OrderSource(
                $autoMaterialDto->orderSources[0]->orderNumber,
                1,
                $material,
                $supplier,
                $material->getCompany()
            );
            $orderSource->setAmountPerPurchaseUnit($autoMaterialDto->orderSources[0]->amountPerPurchaseUnit);
            $orderSource->setLastPriceUpdate(new DateTimeImmutable());
            $orderSource->setPrice($autoMaterialDto->orderSources[0]->price);
            
            $this->entityManager->persist($orderSource);
        }
        
        if ($autoMaterialDto->imgFile) {
            $this->fileService->addFileToEntity($autoMaterialDto->imgFile, $material, 'profileImage', 'Profilbild');
        }

        $material->setName($autoMaterialDto->name ?? '?');
        $material->annotate($autoMaterialDto->note);
        $material->setUnit($autoMaterialDto->unit);
        $material->setManufacturerName($autoMaterialDto->manufacturerName);
        $material->setManufacturerNumber($autoMaterialDto->manufacturerNumber);

        if ($autoMaterialDto->sellingPrice) {
            $material->setSellingPrice(Money::EUR($autoMaterialDto->sellingPrice * 100));
        } else {
            $material->setSellingPrice(null);
        }
        
        $material->setAutoStatus(null);
    
        $material->setUpdatedAt(new DateTimeImmutable());
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $material));

        return $material;
    }
    
    public function patchManyFromDto(ManyDto $patchManyDto, string $userId)
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        
        foreach ($patchManyDto->ids as $id) {
            $material = $this->materialRepository->find($id);
            if (!$material) {
                throw MissingDataException::forEntityNotFound($id, Material::class);
            }
            
            foreach ($patchManyDto->query as $key => $value) {
                if ($key === 'orderStatus') {
                    $material->updateOrderStatus(OrderStatus::fromString($value), $user);
                }
                if ($key === 'isArchived') {
                    $material->setIsArchived($value);
                }
                $material->setUpdatedAt(new DateTimeImmutable());
                $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $material));
            }
        }
    }
    
    /**
     * @param MaterialBatchUpdatesDto $batchUpdateDto
     * @return Material[]
     */
    public function batchUpdateFromDto(BatchUpdateDtoInterface $batchUpdateDto): iterable
    {
        $this->validate($batchUpdateDto);
        
        $updatedMaterials = [];
        foreach ($batchUpdateDto->materialBatchUpdates as $item) {
            $material = $this->materialRepository->find($item->id);
            if (!$material) {
                throw MissingDataException::forEntityNotFound($item->id, Material::class);
            }
            $material = $this->patchEntityFromDto($item, $material);
            $updatedMaterials[] = $material;
        }
        return $updatedMaterials;
    }
    
    /**
     * @param MaterialBatchUpdateDto $dto
     * @param Material $entity
     */
    public function patchEntityFromDto(DtoInterface $dto, object $entity): Material
    {
        $company = $this->currentUserProvider->getCompany();
        
        $dtoArray = (array) $dto;
        
        if (array_key_exists('name', $dtoArray)) {
            $entity->setName($dto->name ?? '?');
        }
    
        if (array_key_exists('itemGroup', $dtoArray)) {
            $itemGroup = null;
            if ($dto->itemGroup) {
                $itemGroup = $this->itemGroupService->getMaterialGroup($company, $dto->itemGroup);
            }
            $entity->setItemGroup($itemGroup);
        }
        
        if (array_key_exists('mainLocation', $dtoArray)) {
            $materialLocation = $entity->getMainLocationLink();
            if ($dto->mainLocation) {
                if ($materialLocation) {
                    $location = $this->locationService->mapStringToLocation($dto->mainLocation, $company);
                    $materialLocation->setLocation($location);
                } else {
                    $materialLocationDto = new MaterialLocationDto();
                    $materialLocationDto->name = $dto->mainLocation;
                    $this->materialLocationService->addLocationToMaterial($materialLocationDto, $entity);
                }
            } else {
                if ($materialLocation) {
                    throw InvalidArgumentException::forBatchUpdateNotAllowed("mainLocation");
                }
            }
        }
        
        $mainLocationLink = $entity->getMainLocationLink();
        
        if (array_key_exists('mainLocationStock', $dtoArray) && array_key_exists('mainLocationAdditionalStock', $dtoArray) && $mainLocationLink) {
            $currentStock = $mainLocationLink->getCurrentStock();
            $newStock = (float) $dto->mainLocationStock;
            $change = $newStock - $currentStock;
            
            $currentStockAlt = $mainLocationLink->getCurrentStockAlt();
            $newStockAlt = (float) $dto->mainLocationAdditionalStock;
            $changeAlt = $newStockAlt - $currentStockAlt;
            
            $stockChange = new StockChange(
                $entity->getCompany(),
                $this->currentUserProvider->getAuthenticatedUser(),
                '',
                $mainLocationLink,
                $change,
                $changeAlt,
                $newStock,
                $newStockAlt,
                null
            );
            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $stockChange));
            $this->entityManager->persist($stockChange);
            $entity->getMainLocationLink()->setCurrentStock($newStock);
            $entity->getMainLocationLink()->setCurrentStockAlt($newStockAlt);
        } else if (array_key_exists('mainLocationStock', $dtoArray) && $mainLocationLink) {
            $currentStock = $mainLocationLink->getCurrentStock();
            $newStock = (float) $dto->mainLocationStock;
            $change = $newStock - $currentStock;
    
            $stockChange = new StockChange(
                $entity->getCompany(),
                $this->currentUserProvider->getAuthenticatedUser(),
                '',
                $mainLocationLink,
                $change,
                0,
                $newStock,
                $mainLocationLink->getCurrentStockAlt(),
                null
            );
            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $stockChange));
            $this->entityManager->persist($stockChange);
            $entity->getMainLocationLink()->setCurrentStock($newStock);
        } else if (array_key_exists('mainLocationAdditionalStock', $dtoArray) && $mainLocationLink) {
            $currentStockAlt = $mainLocationLink->getCurrentStockAlt();
            $newStockAlt = (float) $dto->mainLocationAdditionalStock;
            $changeAlt = $newStockAlt - $currentStockAlt;
    
            $stockChange = new StockChange(
                $entity->getCompany(),
                $this->currentUserProvider->getAuthenticatedUser(),
                '',
                $mainLocationLink,
                0,
                $changeAlt,
                $mainLocationLink->getCurrentStock(),
                $newStockAlt,
                null
            );
            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $stockChange));
            $this->entityManager->persist($stockChange);
            $entity->getMainLocationLink()->setCurrentStockAlt($newStockAlt);
        }
        
        if (array_key_exists('minStock', $dtoArray)) {
            $mainLocation = $entity->getMainLocationLink();
            if ($mainLocation) {
                $mainLocation->setMinStock((float) $dto->minStock);
            }
        }
        
        if (array_key_exists('maxStock', $dtoArray)) {
            $mainLocation = $entity->getMainLocationLink();
            if ($mainLocation) {
                $mainLocation->setMaxStock((float) $dto->maxStock);
            }
        }
        
        if (array_key_exists('mainSupplier', $dtoArray)) {
            $materialOrderSource = $entity->getMainOrderSource();
            if ($dto->mainSupplier) {
                if ($materialOrderSource) {
                    throw InvalidArgumentException::forBatchUpdateNotAllowed("mainOrderSource supplier name");
                } else {
                    $orderSourceDto = new OrderSourceDto();
                    $supplierDto = new SupplierDto();
                    $supplierDto->name = $dto->mainSupplier;
                    $orderSourceDto->supplier = $supplierDto;
                    $orderSourceDto->orderNumber = '?';
                    $this->orderSourceService->addOrderSourceToMaterial($orderSourceDto, $entity);
                }
            } else {
                if ($materialOrderSource) {
                    throw InvalidArgumentException::forBatchUpdateNotAllowed("mainOrderSource");
                }
            }
        }
        
        if (array_key_exists('mainSupplierOrderNumber', $dtoArray)) {
            $materialOrderSource = $entity->getMainOrderSource();
            $materialOrderSource->setOrderNumber($dto->mainSupplierOrderNumber);
        }
        
        if (array_key_exists('mainSupplierPurchasingPrice', $dtoArray)) {
            $materialOrderSource = $entity->getMainOrderSource();
            $materialOrderSource->setPrice((float) $dto->mainSupplierPurchasingPrice);
        }
        
        if (array_key_exists('sellingPrice', $dtoArray)) {
            if ($dto->sellingPrice) {
                $entity->setSellingPrice(Money::EUR(((float) $dto->sellingPrice) * 100));
            } else {
                $entity->setSellingPrice(null);
            }
        }
        
        if (array_key_exists('orderAmount', $dtoArray)) {
            if ($dto->orderAmount) {
                $entity->setOrderAmount((float) $dto->orderAmount);
            } else {
                $entity->setOrderAmount(null);
            }
        }
        
        if (array_key_exists('manufacturerNumber', $dtoArray)) {
            $entity->setManufacturerNumber($dto->manufacturerNumber);
        }
        
        if (array_key_exists('manufacturerName', $dtoArray)) {
            $entity->setManufacturerName($dto->manufacturerName);
        }
        
        if (array_key_exists('barcode', $dtoArray)) {
            $entity->setBarcode($dto->barcode);
        }
        
        if (array_key_exists('unit', $dtoArray)) {
            $entity->setUnit($dto->unit);
        }
        
        if (array_key_exists('permissionGroup', $dtoArray)) {
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
        
        if (array_key_exists('note', $dtoArray)) {
            $entity->annotate($dto->note);
        }
        
        if (array_key_exists('usableTill', $dtoArray)) {
            if ($dto->usableTill) {
                $entity->setUsableTill(new DateTimeImmutable($dto->usableTill));
            } else {
                $entity->setUsableTill(null);
            }
        }
        
        if (array_key_exists('unitAlt', $dtoArray)) {
            $entity->setunitAlt($dto->unitAlt);
        }
        
        $dtoArray = (array) $dto;
        
        if (array_key_exists('unitConversion', $dtoArray)) {
            if ($dtoArray['unitConversion']) {
                $entity->setUnitConversion((float) $dtoArray['unitConversion']);
            } else {
                $entity->setUnitConversion(null);
            }
        }
        
        if (array_key_exists('permanentInventory', $dtoArray)) {
            $entity->setPermanentInventory(!!$dtoArray['permanentInventory']);
        }
        
        if (array_key_exists('customFields', $dtoArray)) {
            $currentCustomFields = $entity->getCustomFields();
            foreach ($dto->customFields as $index => $customField) {
                $currentCustomFields[$index] = $customField;
            }
            $entity->setcustomFields($currentCustomFields);
        }
        
        if (array_key_exists('profileImage', $dtoArray)) {
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
     * @param CreateMaterialDto $dto
     */
    public function prepareImport(DtoInterface $dto, string $userId): ?CreateMaterialDto
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
    
        if ($dto->altScannerIds) {
            foreach ($dto->altScannerIds as $altScannerId) {
                $existingEntity = $this->materialRepository->findByAltScannerId($altScannerId);
                if ($existingEntity) {
                    return null;
                }
            }
        }
        return $dto;
    }
    
    public function createMaterialFromMaterial(Material $material): Material
    {
        $itemNumber = (string) $this->itemNumberService->getNextItemNumber(Material::class, $material->getCompany());
        $materialCopy = new Material($itemNumber, $material->getName(), $material->getCompany());
        $baseMaterial = new CreateMaterialDto();
        $baseMaterial->manufacturerNumber = $material->getManufacturerNumber();
        $baseMaterial->manufacturerName = $material->getManufacturerName();
        $baseMaterial->barcode = $material->getBarcode();
        $baseMaterial->permanentInventory = $material->isPermanentInventory();
        $baseMaterial->unit = $material->getUnit();
        $baseMaterial->unitAlt = $material->getUnitAlt();
        $baseMaterial->unitConversion = $material->getUnitConversion();
        $baseMaterial->note = $material->getNote();
        $baseMaterial->usableTill = $material->getUsableTill();
        $baseMaterial->sellingPrice = $material->getSellingPrice();
        $baseMaterial->customFields = $material->getCustomFields();
        $baseMaterial->orderAmount = $material->getOrderAmount();
        if ($material->getProfileImage()) {
            $url = $this->apiUrl . '/' . $material->getProfileImage();
            $this->fileService->addFileToEntity($this->downloader->downloadCompanyUrl($url), $materialCopy, 'profileImage', 'Profilbild');
        }
    
        $materialCopy = $this->setData($materialCopy, $baseMaterial);
        
        $materialCopy->setItemGroup($material->getItemGroup());
        $materialCopy->setPermissionGroup($material->getPermissionGroup());
        
        $this->entityManager->persist($materialCopy);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $materialCopy));
        
        return $materialCopy;
    }
}
