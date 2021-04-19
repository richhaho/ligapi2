<?php


namespace App\Api\Mapper;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\MaterialLocationDto;
use App\Api\Dto\StocktakingDto;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\LocationCategory;
use App\Entity\Material;
use App\Entity\MaterialLocation;
use App\Entity\Project;
use App\Entity\StockChange;
use App\Entity\User;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\InconsistentDataException;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\LocationRepository;
use App\Repository\MaterialLocationRepository;
use App\Repository\MaterialRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Services\CurrentUserProvider;
use App\Services\LocationService;
use App\Services\MaterialLocationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MaterialLocationMapper implements MapperInterface
{
    use ValidationTrait;
    
    const MAINCATEGORY = 'main';
    
    private EntityManagerInterface $em;
    private MaterialLocationService $materialLocationService;
    private LocationService $locationService;
    private CurrentUserProvider $currentUserProvider;
    private MaterialLocationRepository $materialLocationRepository;
    private EventDispatcherInterface $eventDispatcher;
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    private ProjectRepository $projectRepository;
    private MaterialRepository $materialRepository;
    private UserRepository $userRepository;
    private RequestContext $requestContext;
    private LocationRepository $locationRepository;
    
    public function __construct(
        EntityManagerInterface $em,
        CurrentUserProvider $currentUserProvider,
        MaterialLocationService $materialLocationService,
        LocationService $locationService,
        MaterialLocationRepository $materialLocationRepository,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        ProjectRepository $projectRepository,
        MaterialRepository $materialRepository,
        UserRepository $userRepository,
        RequestContext $requestContext,
        LocationRepository $locationRepository
    )
    {
        $this->em = $em;
        $this->materialLocationService = $materialLocationService;
        $this->locationService = $locationService;
        $this->currentUserProvider = $currentUserProvider;
        $this->materialLocationRepository = $materialLocationRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->projectRepository = $projectRepository;
        $this->materialRepository = $materialRepository;
        $this->userRepository = $userRepository;
        $this->requestContext = $requestContext;
        $this->locationRepository = $locationRepository;
    }
    
    public function supports(string $dtoName): bool
    {
        return $dtoName === MaterialLocationDto::class;
    }
    
    /**
     * @param MaterialLocationDto $dto
     */
    public function createEntityFromDto(DtoInterface $dto, string $userId): MaterialLocation
    {
        $this->validate($dto);
    
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        
        $material = $this->materialRepository->find($dto->material->id);
        
        if (!$material) {
            throw MissingDataException::forEntityNotFound($dto->material->id, Material::class);
        }
        
        if ($dto->name && $this->materialLocationService->materialAlreadyHasLocationWithSameName($material, $dto->name)) {
            throw InconsistentDataException::forDublicateLocationName($dto->name);
        }
        if ($dto->project && $this->materialLocationService->materialAlreadyHasProject($material, $dto->project)) {
            throw InconsistentDataException::forDublicateProjectName($dto->name);
        }
        
        $materialLocation = $this->materialLocationService->addLocationToMaterial(
            $dto,
            $material
        );
    
        $project = null;
        if ($dto->project && $dto->project->id) {
            /** @var Project $project */
            $project = $this->projectRepository->find($dto->project->id);
            if (!$project) {
                throw MissingDataException::forEntityNotFound($dto->project->id, Project::class);
            }
        }
    
        $stockChange = new StockChange(
            $user->getCompany(),
            $user,
            $dto->stockChangeNote,
            $materialLocation,
            $dto->currentStock,
            $dto->currentStockAlt,
            $dto->currentStock,
            $dto->currentStockAlt,
            $project,
            null
        );
        $this->entityManager->persist($stockChange);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $materialLocation));
    
        return $materialLocation;
    }
    
    /**
     * @param MaterialLocationDto $dto
     * @param MaterialLocation $entity
     */
    public function putEntityFromDto(DtoInterface $dto, object $entity): MaterialLocation
    {
        $this->validate($dto);
        
        $user = $this->currentUserProvider->getAuthenticatedUser();
    
        if ($dto->name && $this->materialLocationService->materialAlreadyHasLocationWithSameName($entity->getMaterial(), $dto->name, $entity->getId())) {
            throw InconsistentDataException::forDublicateLocationName($dto->name);
        }
        if ($dto->locationCategory === 'project' && $dto->project && $this->materialLocationService->materialAlreadyHasProject($entity->getMaterial(), $dto->project, $entity->getId())) {
            throw InconsistentDataException::forDublicateProjectName($dto->name);
        }
        
        if ($dto->name) {
            $location = $this->locationService->mapStringToLocation($dto->name, $user->getCompany());
            $entity->setLocation($location);
        }
        
        if ($dto->locationCategory === self::MAINCATEGORY && $entity->getLocationCategory() !== self::MAINCATEGORY) {
            $existingMainMaterialLocation = $this->materialLocationRepository->getMainMaterialLocationsOfMaterial($entity->getMaterial()->getId());
            if (count($existingMainMaterialLocation) > 0) {
                throw InconsistentDataException::forDublicateMainLocation($entity->getLocation()->getName(), $entity->getId(), $entity->getName());
            }
        }
        $entity->setLocationCategory(LocationCategory::fromString($dto->locationCategory));
        
        if ($entity->getCurrentStock() !== $dto->currentStock || $entity->getCurrentStockAlt() !== $dto->currentStockAlt) {
            $amount = $dto->currentStock - $entity->getCurrentStock();
            $amountAlt = $dto->currentStockAlt - $entity->getCurrentStockAlt();
            $project = null;
            if ($dto->project && $dto->project->id) {
                /** @var Project $project */
                $project = $this->projectRepository->find($dto->project->id);
                if (!$project) {
                    throw MissingDataException::forEntityNotFound($dto->project->id, Project::class);
                }
            }
            $stockChange = new StockChange(
                $entity->getCompany(),
                $user,
                $dto->stockChangeNote,
                $entity,
                $amount,
                $amountAlt,
                $dto->currentStock,
                $dto->currentStockAlt,
                $project,
                null
            );
            $this->entityManager->persist($stockChange);
        }
        
        $entity->setMinStock($dto->minStock);
        $entity->setMaxStock($dto->maxStock);
        $entity->setCurrentStock($dto->currentStock);
        $entity->setCurrentStockAlt($dto->currentStockAlt);
        
        $this->materialLocationService->updateOrderStatusOnStockChange($entity);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
        
        return $entity;
    }
    
    public function updateMaterialLocationStock(StocktakingDto $stocktakingDto, MaterialLocation $materialLocation): MaterialLocation
    {
        $this->validate($stocktakingDto);
        
        $user = $this->currentUserProvider->getAuthenticatedUser();
        
        $amount = $stocktakingDto->currentStock - $materialLocation->getCurrentStock();
        $amountAlt = $stocktakingDto->currentStockAlt - $materialLocation->getCurrentStockAlt();
        $project = null;
        if ($stocktakingDto->projectId) {
            /** @var Project $project */
            $project = $this->projectRepository->find($stocktakingDto->projectId);
            if (!$project) {
                throw MissingDataException::forEntityNotFound($stocktakingDto->projectId, Project::class);
            }
        }
        $stockChange = new StockChange(
            $materialLocation->getCompany(),
            $user,
            $stocktakingDto->stockChangeNote,
            $materialLocation,
            $amount,
            $amountAlt,
            $stocktakingDto->currentStock,
            $stocktakingDto->currentStockAlt,
            $project,
            null
        );
        $this->entityManager->persist($stockChange);
    
        $materialLocation->setCurrentStock($stocktakingDto->currentStock);
        $materialLocation->setCurrentStockAlt($stocktakingDto->currentStockAlt);
        
        return $materialLocation;
    }
    
    /**
     * @param MaterialLocationDto $dto
     * @param MaterialLocation $entity
     */
    public function patchEntityFromDto(DtoInterface $dto, object $entity): MaterialLocation
    {
        if (isset($dto->name)) {
            $user = $this->currentUserProvider->getAuthenticatedUser();
    
            if ($this->materialLocationService->materialAlreadyHasLocationWithSameName($entity->getMaterial(), $dto->name, $entity->getId())) {
                throw InconsistentDataException::forDublicateLocationName($dto->name);
            }
    
            $location = $this->locationService->mapStringToLocation($dto->name, $user->getCompany());
            $entity->setLocation($location);
        }
        return $entity;
    }
    
    /**
     * @param MaterialLocationDto $dto
     */
    public function prepareImport(DtoInterface $dto, string $userId): ?MaterialLocationDto
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $this->validate($dto);
        
        $existingLocation = $this->locationService->mapStringToLocation($dto->name, $user->getCompany());
    
        $material = null;
        if ($dto->material->id) {
            $material = $this->materialRepository->find($dto->material->id);
        } else if ($dto->material->originalId) {
            $material = $this->materialRepository->findByAltScannerId($dto->material->originalId);
        }
        
        if (!$material) {
            throw MissingDataException::forEntityNotFound('ID/OriginalId', Material::class);
        }
        
        $dto->material->id = $material->getId();
        
        $existingMaterialLocation = $this->materialLocationRepository->findByMaterialIdAndLocationId($material->getId(), $existingLocation->getId());
        
        if ($existingMaterialLocation) {
            return null;
        }
        
        return $dto;
    }
}
