<?php

declare(strict_types=1);


namespace App\Services;


use App\Api\Dto\ConsignmentAddMultiple;
use App\Api\Dto\CreateConsignmentItemDto;
use App\Api\Dto\PdfDocumentDto;
use App\Api\Mapper\ConsignmentItemMapper;
use App\Entity\Consignment;
use App\Entity\ConsignmentItem;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\ConsignmentItemStatus;
use App\Entity\Material;
use App\Entity\StockChange;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\InconsistentDataException;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\ConsignmentItemRepository;
use App\Repository\ConsignmentRepository;
use App\Repository\KeyyRepository;
use App\Repository\MaterialRepository;
use App\Repository\PdfDocumentTypeRepository;
use App\Repository\ToolRepository;
use App\Services\Pdf\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConsignmentService
{
    private PdfDocumentTypeRepository $pdfDocumentTypeRepository;
    private PdfService $pdfService;
    private ConsignmentItemRepository $consignmentItemRepository;
    private string $publicPath;
    private CurrentUserProvider $currentUserProvider;
    private ConsignmentRepository $consignmentRepository;
    private ItemNumberService $itemNumberService;
    private EntityManagerInterface $entityManager;
    private MaterialRepository $materialRepository;
    private ToolRepository $toolRepository;
    private KeyyRepository $keyyRepository;
    private ConsignmentItemMapper $consignmentItemMapper;
    private EventDispatcherInterface $eventDispatcher;
    private MaterialLocationService $materialLocationService;
    
    public function __construct(
        PdfDocumentTypeRepository $pdfDocumentTypeRepository,
        PdfService $pdfService,
        ConsignmentItemRepository $consignmentItemRepository,
        ConsignmentRepository $consignmentRepository,
        ItemNumberService $itemNumberService,
        string $publicPath,
        CurrentUserProvider $currentUserProvider,
        EntityManagerInterface $entityManager,
        MaterialRepository $materialRepository,
        MaterialLocationService $materialLocationService,
        ToolRepository $toolRepository,
        KeyyRepository $keyyRepository,
        ConsignmentItemMapper $consignmentItemMapper,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->pdfDocumentTypeRepository = $pdfDocumentTypeRepository;
        $this->pdfService = $pdfService;
        $this->consignmentItemRepository = $consignmentItemRepository;
        $this->publicPath = $publicPath;
        $this->currentUserProvider = $currentUserProvider;
        $this->consignmentRepository = $consignmentRepository;
        $this->itemNumberService = $itemNumberService;
        $this->entityManager = $entityManager;
        $this->materialRepository = $materialRepository;
        $this->toolRepository = $toolRepository;
        $this->keyyRepository = $keyyRepository;
        $this->consignmentItemMapper = $consignmentItemMapper;
        $this->eventDispatcher = $eventDispatcher;
        $this->materialLocationService = $materialLocationService;
    }

    private function splitConsignmentItemIdsIntoConsignments(array $ids): array
    {
        $idsArray = [];
    
        foreach ($ids as $id) {
            $consignmentId = $this->consignmentItemRepository->getConsignmentId($id);
            $idsArray[$consignmentId][] = $id;
        }
        
        return $idsArray;
    }
    
    public function generateConsignmentPdf(PdfDocumentDto $pdfDocumentDto): string
    {
        $documents = [];
        
        $splitIds = $this->splitConsignmentItemIdsIntoConsignments($pdfDocumentDto->ids);
        
        foreach ($pdfDocumentDto->pdfDocumentTypeIds as $pdfDocumentTypeId) {
            $pdfDocumentType = $this->pdfDocumentTypeRepository->find($pdfDocumentTypeId);
            foreach ($splitIds as $splitId) {
                $newLabelDto = new PdfDocumentDto();
                $newLabelDto->ids = $splitId;
                $newLabelDto->entityType = $pdfDocumentDto->entityType;
                $documents[] = $this->pdfService->createDocumentFromPdfDocumentDtoAndPdfDocumentType($newLabelDto, $pdfDocumentType);
            }
        }
        
        $companyId = $this->currentUserProvider->getCompany()->getId();
    
        $folder = $this->publicPath . '/companyData/' . $companyId . '/pdf/';
        $fileName = "kommission.pdf";
    
        $outputName = $folder . $fileName;
    
        $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outputName ";
        //Add each pdf file to the end of the command
        foreach($documents as $file) {
            $cmd .= $file." ";
        }
        shell_exec($cmd);
        
        return 'companyData/' . $companyId . '/pdf/' . $fileName;
    }
    
    public function addMultiplePositionsToConsignment(ConsignmentAddMultiple $consignmentAddMultiple): iterable
    {
        $company = $this->currentUserProvider->getCompany();
        
        $updatedEntities = [];
        
        if ($consignmentAddMultiple->consignmentId) {
            $consignment = $this->consignmentRepository->find($consignmentAddMultiple->consignmentId);
            if (!$consignment) {
                throw MissingDataException::forEntityNotFound($consignmentAddMultiple->consignmentId, Consignment::class);
            }
        } else {
            $consignment = new Consignment(
                $company,
                $this->itemNumberService->getNextItemNumber(Consignment::class, $company),
                null,
                null,
                $consignmentAddMultiple->consignmentName
            );
            $this->entityManager->persist($consignment);
            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $consignment));
        }
        
        foreach ($consignmentAddMultiple->consignmentPositions as $consignmentPosition) {
            $consignmentItemDto = new CreateConsignmentItemDto();
    
            switch ($consignmentPosition->entityType) {
                case 'material':
                    $consignmentItemDto->materialId = $consignmentPosition->id;
                    break;
                case 'tool':
                    $consignmentItemDto->toolId = $consignmentPosition->id;
                    break;
                case 'keyy':
                    $consignmentItemDto->keyyId = $consignmentPosition->id;
                    break;
                default:
                    $consignmentItemDto->manualName = $consignmentPosition->name;
            }
            $consignmentItemDto->amount = $consignmentPosition->amount;
            $consignmentItemDto->consignmentId = $consignment->getId();
            
            $consignmentItem = $this->consignmentItemMapper->createConsignmentItemFromDto($consignmentItemDto);
            $this->entityManager->persist($consignmentItem);
            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $consignmentItem));
    
            switch ($consignmentPosition->entityType) {
                case 'material':
                    $updatedEntities[] = $consignmentItem->getMaterial();
                    break;
                case 'tool':
                    $updatedEntities[] = $consignmentItem->getTool();
                    break;
                case 'keyy':
                    $updatedEntities[] = $consignmentItem->getKeyy();
                    break;
            }
        }
        return $updatedEntities;
    }
    
    private function bookConsignmentItemFromMainStock(ConsignmentItem $consignmentItem): Material
    {
        $currentUser = $this->currentUserProvider->getAuthenticatedUser();
        $linkedItem = $consignmentItem->getConsignmentItemSubject();
        if (!$linkedItem instanceof Material) {
            throw InvalidArgumentException::forInvalidEntityType(get_class($linkedItem), Material::class);
        }
        $mainLocationLink = $linkedItem->getMainLocationLink();
        if (!$mainLocationLink) {
            throw InvalidArgumentException::forMainLocationLinkMissing($linkedItem);
        }
        
        $remainingAmount = $consignmentItem->getAmount() - $consignmentItem->getConsignedAmount();
        
        $consignmentItem->setConsignedAmount($consignmentItem->getAmount());
        $consignmentItem->setConsignmentItemStatus(ConsignmentItemStatus::complete());
        
        if (!$linkedItem->isPermanentInventory()) {
            return $linkedItem;
        }
        
        $currentStock = $mainLocationLink->getCurrentStock();
        $newStock = $currentStock - $remainingAmount;
    
        $newStockAlt = 0;
        $currentStockAlt = 0;
        if ($linkedItem->getUnitConversion()) {
            $currentStockAlt = $mainLocationLink->getCurrentStockAlt();
            $newStockAlt = $currentStockAlt - $remainingAmount * $linkedItem->getUnitConversion();
            if ($currentStockAlt < 0) {
                throw InconsistentDataException::forNegativeStock($mainLocationLink->getName(), $newStockAlt);
            }
        }
        
        if ($newStock < 0) {
            throw InconsistentDataException::forNegativeStock($mainLocationLink->getName(), $newStock);
        }
        
        $stockChange = new StockChange(
            $currentUser->getCompany(),
            $currentUser,
            'Kommission' . $consignmentItem->getConsignment()->getName(),
            $mainLocationLink,
            $newStock - $currentStock,
            $newStockAlt - $currentStockAlt,
            $newStock,
            $newStockAlt,
            $consignmentItem->getConsignment()->getProject()
        );
        
        $mainLocationLink->setCurrentStock($newStock);
        $mainLocationLink->setCurrentStockAlt($newStockAlt);
        $this->materialLocationService->updateOrderStatusOnStockChange($mainLocationLink);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $consignmentItem));
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $stockChange));
        
        $this->entityManager->persist($stockChange);
        
        return $linkedItem;
    }
    
    public function bookConsignmentItemsFromMainStock(array $consignmentItems): iterable
    {
        $materials = [];
        foreach ($consignmentItems as $consignmentItem) {
            $materials[] = $this->bookConsignmentItemFromMainStock($consignmentItem);
        }
        return $materials;
    }
    
    /**
     * @param ConsignmentItem[] $consignmentItems
     */
    public function resolveConsignmentItems(array $consignmentItems): void
    {
        foreach ($consignmentItems as $consignmentItem) {
            $consignmentItem->setConsignedAmount($consignmentItem->getAmount());
            $consignmentItem->setConsignmentItemStatus(ConsignmentItemStatus::complete());
            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $consignmentItem));
        }
    }
}
