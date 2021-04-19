<?php

declare(strict_types=1);


namespace App\Services;


use App\Api\Dto\QuickBookDto;
use App\Entity\Data\AutoStatus;
use App\Entity\Data\ChangeAction;
use App\Entity\Material;
use App\Entity\MaterialLocation;
use App\Entity\OrderSource;
use App\Entity\StockChange;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\MaterialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MaterialService
{
    private OrderSourceService $orderSourceService;
    private EntityManagerInterface $entityManager;
    private MaterialRepository $materialRepository;
    private CurrentUserProvider $currentUserProvider;
    private EventDispatcherInterface $eventDispatcher;
    
    public function __construct(
        OrderSourceService $orderSourceService,
        MaterialRepository $materialRepository,
        EntityManagerInterface $entityManager,
        CurrentUserProvider $currentUserProvider,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->orderSourceService = $orderSourceService;
        $this->entityManager = $entityManager;
        $this->materialRepository = $materialRepository;
        $this->currentUserProvider = $currentUserProvider;
        $this->eventDispatcher = $eventDispatcher;
    }
    
    public function deleteMaterialWithRelatedEntities(Material $material)
    {
        $material->setLastMaterialOrderPosition(null);
        
        /** @var OrderSource $orderSource */
        foreach ($material->getOrderSources() as $orderSource) {
            foreach ($orderSource->getPriceUpdates() as $priceUpdate) {
                $this->entityManager->remove($priceUpdate);
            }
            foreach ($orderSource->getMaterialOrderPositions() as $materialOrderPosition) {
                $this->entityManager->remove($materialOrderPosition);
            }
            foreach ($orderSource->getDirectOrderPositionResults() as $directOrderPositionResult) {
                $this->entityManager->remove($directOrderPositionResult);
            }
            $this->entityManager->flush();
            $this->entityManager->remove($orderSource);
        }
    
        /** @var MaterialLocation $materialLocation */
        foreach ($material->getMaterialLocations() as $materialLocation) {
            foreach ($materialLocation->getStockChanges() as $stockChange) {
                $this->entityManager->remove($stockChange);
            }
            $this->entityManager->remove($materialLocation);
        }
    
        foreach ($material->getAllTasks() as $task) {
            $this->entityManager->remove($task);
        }
    
        foreach ($material->getConsignmentItems() as $consignmentItem) {
            $this->entityManager->remove($consignmentItem);
        }
    
        $this->entityManager->flush();
        
        $this->entityManager->remove($material);
    }
    
    public function setStatusOfMaterials(array $materialsWithGetData, AutoStatus $autoStatus)
    {
        foreach ($materialsWithGetData as $materialWithGetData) {
            $material = $this->materialRepository->find($materialWithGetData);
            if (!$material) {
                throw MissingDataException::forEntityNotFound($material, Material::class);
            }
            $material->setAutoStatus($autoStatus);
        }
        $this->entityManager->flush();
    }
    
    public function getNextBatchOfMaterialIdsWithCrawlerSearchTerm(int $limit = null)
    {
        /** @var Material $firstMaterialWithCrawlerSearchTerm */
        $firstMaterialWithCrawlerSearchTerm = $this->materialRepository->getOldestMaterialWithAutoSearchTerm();
        if (!$firstMaterialWithCrawlerSearchTerm) {
            return 0;
        }
        
        /** @var Material[] $allMaterialsWithCrawlerSearchTerm */
        $allMaterialsWithCrawlerSearchTerm = $this->materialRepository
            ->getMaterialsWithAutoSearchTerm($firstMaterialWithCrawlerSearchTerm->getCompany(), $limit);
        
        return $allMaterialsWithCrawlerSearchTerm;
    }
    
    public function quickBook(QuickBookDto $quickBookDto): string
    {
        $user = $this->currentUserProvider->getAuthenticatedUser();
        $materials = $this->materialRepository->findMaterialByCode($quickBookDto->code);
        if (count($materials) === 0) {
            return 'Not found';
        }
        $firstMaterial = $materials[0];
        if (!$firstMaterial->isPermanentInventory()) {
            return 'Is not permanent inventory';
        }
        $mainLocation = $firstMaterial->getMainLocationLink();
        if (!$mainLocation) {
            return 'Has no main location';
        }
        $currentStock = $mainLocation->getCurrentStock();
        $newStock = $currentStock + $quickBookDto->amount;
        if ($newStock < 0) {
            return 'Insufficient stock';
        }
        $stockChange = new StockChange(
            $user->getCompany(),
            $user,
            $quickBookDto->note,
            $mainLocation,
            $quickBookDto->amount,
            0,
            $newStock,
            $mainLocation->getCurrentStockAlt(),
            null
        );
        $mainLocation->setCurrentStock($newStock);
        $this->entityManager->persist($stockChange);
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $stockChange));
        
        return 'OK (' . $newStock . ')';
    }
}
