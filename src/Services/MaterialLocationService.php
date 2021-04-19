<?php


namespace App\Services;


use App\Api\Dto\MaterialLocationDto;
use App\Api\Dto\ProjectDto;
use App\Api\Mapper\ValidationTrait;
use App\Entity\Data\LocationCategory;
use App\Entity\Data\OrderStatus;
use App\Entity\Material;
use App\Entity\MaterialLocation;
use App\Entity\Project;
use App\Exceptions\Domain\InconsistentDataException;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\MaterialLocationRepository;
use App\Repository\MaterialRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MaterialLocationService
{
    use ValidationTrait;
    
    private LocationService $locationService;
    private EntityManagerInterface $entityManager;
    private MaterialRepository $materialRepository;
    private MaterialLocationRepository $materialLocationRepository;
    private ValidatorInterface $validator;
    private CurrentUserProvider $currentUserProvider;
    private ProjectRepository $projectRepository;
    
    public function __construct(
        LocationService $locationService,
        EntityManagerInterface $entityManager,
        MaterialRepository $materialRepository,
        MaterialLocationRepository $materialLocationRepository,
        ProjectRepository $projectRepository,
        ValidatorInterface $validator,
        CurrentUserProvider $currentUserProvider
    )
    {
        $this->locationService = $locationService;
        $this->entityManager = $entityManager;
        $this->materialRepository = $materialRepository;
        $this->materialLocationRepository = $materialLocationRepository;
        $this->validator = $validator;
        $this->currentUserProvider = $currentUserProvider;
        $this->projectRepository = $projectRepository;
    }
    
    public function materialAlreadyHasLocationWithSameName(Material $material, string $locationName, ?string $existingMaterialLocationId = null): bool
    {
        /** @var MaterialLocation[] $existingMaterialLocations */
        $existingMaterialLocations = $this->materialLocationRepository->getMaterialLocationsOfMaterial($material->getId());
    
        foreach ($existingMaterialLocations as $existingMaterialLocation) {
            if ($existingMaterialLocation->getLocation() && $existingMaterialLocation->getLocation()->getName() === $locationName && $existingMaterialLocation->getId() !== $existingMaterialLocationId) {
                return true;
            }
        }
        return false;
    }
    
    public function materialAlreadyHasProject(Material $material, ProjectDto $projectDto, ?string $existingMaterialLocationId = null): bool
    {
        /** @var MaterialLocation[] $existingMaterialLocations */
        $existingMaterialLocations = $this->materialLocationRepository->getMaterialLocationsOfMaterial($material->getId());
    
        foreach ($existingMaterialLocations as $existingMaterialLocation) {
            if ($existingMaterialLocation->getProject() && $existingMaterialLocation->getProject()->getId() === $projectDto->id && $existingMaterialLocation->getId() !== $existingMaterialLocationId) {
                return true;
            }
        }
        return false;
    }
    
    public function addLocationToMaterial(
        MaterialLocationDto $materialLocationDto,
        Material $material
    ): MaterialLocation
    {
        $this->validate($materialLocationDto);
        
        if ($materialLocationDto->name && $this->materialAlreadyHasLocationWithSameName($material, $materialLocationDto->name)) {
            throw InconsistentDataException::forDublicateLocationName($materialLocationDto->name);
        }
        
        if (LocationCategory::fromString($materialLocationDto->locationCategory)->getValue() === LocationCategory::main()->getValue()) {
            $existingMainMaterialLocation = $this->materialLocationRepository->getMainMaterialLocationsOfMaterial($material->getId());
            if (count($existingMainMaterialLocation) > 0) {
                throw InconsistentDataException::forDublicateMainLocation($materialLocationDto->name, $material->getId(), $material->getName());
            }
        }
    
        $location = null;
        if ($materialLocationDto->name) {
            $location = $this->locationService->mapStringToLocation($materialLocationDto->name, $material->getCompany());
        }
    
        $project = null;
        if ($materialLocationDto->project) {
            $project = $this->projectRepository->find($materialLocationDto->project->id);
            if (!$project) {
                throw MissingDataException::forEntityNotFound($materialLocationDto->project->id, Project::class);
            }
        }
        
        if (!$location && !$project) {
            throw InconsistentDataException::forDataIsMissing('materialLocation project and location name');
        }
        
        $locationMaterialLink = new MaterialLocation(
            $material->getCompany(),
            LocationCategory::fromString($materialLocationDto->locationCategory),
            $material,
            $location,
            $project,
            $materialLocationDto->materialLocationId
        );
        $locationMaterialLink->setMinStock($materialLocationDto->minStock);
        $locationMaterialLink->setMaxStock($materialLocationDto->maxStock);
        $locationMaterialLink->setCurrentStock($materialLocationDto->currentStock ? $materialLocationDto->currentStock : 0);
        $locationMaterialLink->setCurrentStockAlt($materialLocationDto->currentStockAlt ? $materialLocationDto->currentStockAlt : 0);
        
        $this->entityManager->persist($locationMaterialLink);
        
        $material->addMaterialLocation($locationMaterialLink);
        
        return $locationMaterialLink;
    }
    
    public function updateOrderStatusOnStockChange(MaterialLocation $materialLocation): void
    {
        $material = $materialLocation->getMaterial();
        if (!$material->isPermanentInventory()) {
            return;
        }
        if (!$materialLocation->getMinStock()) {
            return;
        }
        if ($materialLocation->getLocationCategory() !== 'main') {
            return;
        }
        
        $user = $this->currentUserProvider->getAuthenticatedUser();
        if ($materialLocation->getCurrentStock() < $materialLocation->getMinStock()) {
            $material->updateOrderStatus(OrderStatus::toOrder(), $user);
        }
        
        if ($materialLocation->getCurrentStock() >= $materialLocation->getMinStock()) {
            $material->updateOrderStatus(OrderStatus::available(), $user);
        }
    }
}
