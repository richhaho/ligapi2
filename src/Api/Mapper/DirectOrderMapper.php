<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\DirectOrderDto;
use App\Entity\Data\ChangeAction;
use App\Entity\DirectOrder;
use App\Entity\Supplier;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\SupplierRepository;
use App\Services\CurrentUserProvider;
use App\Services\DirectOrderService;
use App\Services\ItemNumberService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DirectOrderMapper
{
    
    use ValidationTrait;
    
    private CurrentUserProvider $currentUserProvider;
    private EventDispatcherInterface $eventDispatcher;
    private ValidatorInterface $validator;
    private SupplierRepository $supplierRepository;
    private ItemNumberService $itemNumberService;
    private EntityManagerInterface $entityManager;
    private DirectOrderService $directOrderService;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        SupplierRepository $supplierRepository,
        ItemNumberService $itemNumberService,
        EntityManagerInterface $entityManager,
        DirectOrderService $directOrderService
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->supplierRepository = $supplierRepository;
        $this->itemNumberService = $itemNumberService;
        $this->entityManager = $entityManager;
        $this->directOrderService = $directOrderService;
    }
    
    public function createDirectOrderFromDto(DirectOrderDto $directOrderDto): DirectOrder
    {
        $this->validate($directOrderDto);
        $user = $this->currentUserProvider->getAuthenticatedUser();
        
        $mainSupplier = $this->supplierRepository->find($directOrderDto->mainSupplier->id);
        if (!$mainSupplier) {
            throw MissingDataException::forEntityNotFound($directOrderDto->mainSupplier->id, Supplier::class);
        }
    
        $directOrderNumber = $this->itemNumberService->getNextItemNumber(DirectOrder::class, $this->currentUserProvider->getCompany());
        
        $directOrder = new DirectOrder($mainSupplier, $directOrderNumber, $user);
    
        $this->directOrderService->createDirectOrderPositionsAndResultsOfDirectOrderDto($directOrderDto, $directOrder, $mainSupplier);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $directOrder));
        
        return $directOrder;
    }
}
