<?php


namespace App\Services;


use App\Api\Dto\OrderSourceDto;
use App\Api\Mapper\ValidationTrait;
use App\Entity\Data\AutoStatus;
use App\Entity\Material;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use App\Exceptions\Domain\InconsistentDataException;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\OrderSourceRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderSourceService
{
    use ValidationTrait;
    
    private EntityManagerInterface $entityManager;
    private OrderSourceRepository $orderSourceRepository;
    private ValidatorInterface $validator;
    private SupplierService $supplierService;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        OrderSourceRepository $orderSourceRepository,
        ValidatorInterface $validator,
        SupplierService $supplierService
    )
    {
        $this->entityManager = $entityManager;
        $this->orderSourceRepository = $orderSourceRepository;
        $this->validator = $validator;
        $this->supplierService = $supplierService;
    }
    
    public function materialAlreadyHasOrderSourceForSupplier(Material $material, Supplier $supplier): ?OrderSource
    {
        return $this->orderSourceRepository->getOrderSourceOfMaterialAndSupplier($material, $supplier);
    }
    
    public function addOrderSourceToMaterial(
        OrderSourceDto $orderSourceDto,
        Material $material
    ): OrderSource
    {
        $this->validate($orderSourceDto);
    
        $supplier = $this->supplierService->getSupplier($material->getCompany(), $orderSourceDto->supplier->name, $orderSourceDto->supplier->id);
        
        if ($this->materialAlreadyHasOrderSourceForSupplier($material, $supplier)) {
            throw InconsistentDataException::forDublicateSupplier($supplier->getName());
        }
        
        if ($orderSourceDto->priority === 1) {
            $this->setLowPriorityOfMaterialOrderSources($material);
        }
    
        $orderSource = new OrderSource(
            $orderSourceDto->orderNumber,
            $orderSourceDto->priority,
            $material,
            $supplier,
            $material->getCompany()
        );
        
        $orderSource->setAmountPerPurchaseUnit($orderSourceDto->amountPerPurchaseUnit);
        
        $orderSource->setNote($orderSourceDto->note);
    
        $this->entityManager->persist($orderSource);
        
        if ($orderSourceDto->price) {
            $orderSource->setPrice($orderSourceDto->price);
            $orderSource->setLastPriceUpdate(new DateTimeImmutable());
        }
        
        $material->addOrderSource($orderSource);
        
        return $orderSource;
    }
    
    public function setLowPriorityOfMaterialOrderSources(Material $material): void
    {
        /** @var OrderSource[] $otherOrderSources */
        $otherOrderSources = $material->getOrderSources();
        foreach ($otherOrderSources as $otherOrderSource) {
            $otherOrderSource->setPriority(2);
        }
    }
    
    public function getNextBatchOfOrderSourceWithPriceUpdate(int $limit = null)
    {
        /** @var OrderSource $firstOrderSourceForPriceUpdate */
        $firstOrderSourceForPriceUpdate = $this->orderSourceRepository->getOldestOrderSourceWithPriceUpdate();
        if (!$firstOrderSourceForPriceUpdate) {
            return 0;
        }
    
        /** @var OrderSource[] $allOrderSourcesToProcess */
        $allOrderSourcesToProcess = $this->orderSourceRepository
            ->getOrderSourceIdsWithPriceUpdateForSupplier($firstOrderSourceForPriceUpdate->getSupplier(), $limit);
        
        return $allOrderSourcesToProcess;
    }
    
    public function setStatusOfOrderSources(array $orderSourceIdsWithPriceUpdate, AutoStatus $autoStatus)
    {
        foreach ($orderSourceIdsWithPriceUpdate as $orderSourceIdWithPriceUpdate) {
            $orderSource = $this->orderSourceRepository->find($orderSourceIdWithPriceUpdate);
            if (!$orderSource) {
                throw MissingDataException::forEntityNotFound($orderSourceIdWithPriceUpdate, OrderSource::class);
            }
            $orderSource->setAutoStatus($autoStatus);
        }
        $this->entityManager->flush();
    }
    
//    private function getBestPriceForOrderSourceForMaterial(Material $material): OrderSource
//    {
//        $bestPriceOrderSource = $material->getMainOrderSource();
//        $bestPrice = $material->getMainOrderSource()->getPrice();
//        /** @var OrderSource $orderSource */
//        foreach ($material->getOrderSources() as $orderSource) {
//            if ($orderSource->getPrice() < $bestPrice) {
//                $bestPrice = $orderSource->getPrice();
//                $bestPriceOrderSource = $orderSource;
//            }
//        }
//        return $bestPriceOrderSource;
//    }
    
//    public function getBestPriceOrderSourcesForMaterials(DirectOrderDto $orderSourcesPriceRequestDto, Supplier $supplier): array
//    {
//        $result = [];
//        /** @var OrderPositionDto $directOrderPosition */
//        foreach ($orderSourcesPriceRequestDto->directOrderPositions as $directOrderPosition) {
//            $orderSource = $this->orderSourceRepository->getOrdersourceOfSupplierAndOrderNumber($supplier, $directOrderPosition->oderNumber);
//            $result[] = [
//                'orderNumber' => $directOrderPosition->oderNumber,
//                'bestPriceOrderSource' => $this->getBestPriceForOrderSourceForMaterial($orderSource->getMaterial())
//            ];
//        }
//        return $result;
//    }
    
//    public function orderSourcelAlreadyExistsWithSameSupplierAndOrderNumber(Supplier $supplier, string $orderNumber): ?OrderSource
//    {
//        return $this->orderSourceRepository->getOrdersourceOfSupplierAndOrderNumber($supplier, $orderNumber);
//    }
    
//    public function getDirectOrderResponse(DirectOrderDto $directOrderDto): array
//    {
//        return [];
//    }

    public function getOrderSourceOfMaterialIdAndSupplierName(string $materialId, string $supplierName): ?OrderSource
    {
        return $this->orderSourceRepository->getOrderSourceOfMaterialIdAndSupplierId($materialId, $supplierName);
    }
}
