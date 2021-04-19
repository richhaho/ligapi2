<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\MaterialOrderDto;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\MaterialOrderType;
use App\Entity\Data\OrderStatus;
use App\Entity\DirectOrderPositionResult;
use App\Entity\MaterialOrder;
use App\Entity\MaterialOrderPosition;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\DirectOrderPositionResultRepository;
use App\Repository\OrderSourceRepository;
use App\Repository\SupplierRepository;
use App\Services\CurrentUserProvider;
use App\Services\ItemNumberService;
use App\Services\MaterialOrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MaterialOrderMapper
{
    use ValidationTrait;
    
    private CurrentUserProvider $currentUserProvider;
    private ValidatorInterface $validator;
    private OrderSourceRepository $orderSourceRepository;
    private SupplierRepository $supplierRepository;
    private EntityManagerInterface $entityManager;
    private MaterialOrderService $materialOrderService;
    private EventDispatcherInterface $eventDispatcher;
    private ItemNumberService $itemNumberService;
    private DirectOrderPositionResultRepository $directOrderPositionResultRepository;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        ValidatorInterface $validator,
        OrderSourceRepository $orderSourceRepository,
        SupplierRepository $supplierRepository,
        EntityManagerInterface $entityManager,
        MaterialOrderService $materialOrderService,
        EventDispatcherInterface $eventDispatcher,
        ItemNumberService $itemNumberService,
        DirectOrderPositionResultRepository $directOrderPositionResultRepository
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->validator = $validator;
        $this->orderSourceRepository = $orderSourceRepository;
        $this->supplierRepository = $supplierRepository;
        $this->entityManager = $entityManager;
        $this->materialOrderService = $materialOrderService;
        $this->eventDispatcher = $eventDispatcher;
        $this->itemNumberService = $itemNumberService;
        $this->directOrderPositionResultRepository = $directOrderPositionResultRepository;
    }
    
    public function createMaterialOrderFromDto(MaterialOrderDto $materialOrderDto): MaterialOrder
    {
        $this->validate($materialOrderDto);
        
        $company = $this->currentUserProvider->getCompany();
    
        /** @var Supplier $supplier */
        $supplier = $this->supplierRepository->find($materialOrderDto->supplier->id);
        if (!$supplier) {
            throw MissingDataException::forEntityNotFound($materialOrderDto->supplier->id, Supplier::class);
        }
    
        $materialOrderNumber = $this->itemNumberService->getNextItemNumber(MaterialOrder::class, $company);
        
        $materialOrder =  new MaterialOrder(
            MaterialOrderType::fromString($materialOrderDto->materialOrderType),
            $supplier,
            $company,
            $materialOrderNumber,
            $materialOrderDto->deliveryNote
        );
        
        $materialOrder->setConsignmentNumber($materialOrderDto->consignmentNumber);
    
        foreach ($materialOrderDto->materialOrderPositions as $materialOrderPositionDto) {
            /** @var OrderSource $orderSource */
            $orderSource = $this->orderSourceRepository->find($materialOrderPositionDto->orderSource->id);
            if (!$orderSource) {
                throw MissingDataException::forEntityNotFound($materialOrderPositionDto->orderSource->id, OrderSource::class);
            }
            
            $materialOrderPosition = new MaterialOrderPosition(
                $company,
                $materialOrderPositionDto->amount,
                $orderSource,
                $materialOrder,
                $orderSource->getPrice(),
                null,
                $orderSource->getMaterial()->getOrderStatusNote()
            );
            
            $this->entityManager->persist($materialOrderPosition);
            
            $materialOrder->addMaterialOrderPosition($materialOrderPosition);
            
            if ($materialOrderPositionDto->directOrderPositionResult) {
                $directOrderPositionResult = $this->directOrderPositionResultRepository->find($materialOrderPositionDto->directOrderPositionResult->id);
                if (!$directOrderPositionResult) {
                    throw MissingDataException::forEntityNotFound($materialOrderPositionDto->directOrderPositionResult->id, DirectOrderPositionResult::class);
                }
                $directOrderPositionResult->getDirectOrderPosition()->setMaterialOrderPosition($materialOrderPosition);
            }
        }
        
        foreach ($materialOrder->getMaterialOrderPositions() as $materialOrderPosition) {
            $materialOrderPosition->getOrderSource()->getMaterial()->updateOrderStatus(OrderStatus::onItsWay(), $this->currentUserProvider->getAuthenticatedUser());
            $materialOrderPosition->getOrderSource()->getMaterial()->setLastMaterialOrderPosition($materialOrderPosition);
            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $materialOrderPosition->getOrderSource()->getMaterial()));
        }
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $materialOrder));
        
        return $this->materialOrderService->createMaterialOrderFiles($materialOrder);
    }
}
