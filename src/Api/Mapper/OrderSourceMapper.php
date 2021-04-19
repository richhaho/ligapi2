<?php


namespace App\Api\Mapper;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\OrderSourceDto;
use App\Entity\Data\ChangeAction;
use App\Entity\Material;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use App\Entity\User;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Exceptions\Domain\UnsupportedMethodException;
use App\Repository\MaterialRepository;
use App\Repository\SupplierRepository;
use App\Repository\UserRepository;
use App\Services\CurrentUserProvider;
use App\Services\OrderSourceService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OrderSourceMapper implements MapperInterface
{
    use ValidationTrait;
    
    private EntityManagerInterface $entityManager;
    private SupplierRepository $supplierRepository;
    private CurrentUserProvider $currentUserProvider;
    private OrderSourceService $orderSourceService;
    private EventDispatcherInterface $eventDispatcher;
    private ValidatorInterface $validator;
    private UserRepository $userRepository;
    private MaterialRepository $materialRepository;
    private RequestContext $requestContext;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        CurrentUserProvider $currentUserProvider,
        SupplierRepository $supplierRepository,
        OrderSourceService $orderSourceService,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        MaterialRepository $materialRepository,
        RequestContext $requestContext
    )
    {
        $this->entityManager = $entityManager;
        $this->supplierRepository = $supplierRepository;
        $this->currentUserProvider = $currentUserProvider;
        $this->orderSourceService = $orderSourceService;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->materialRepository = $materialRepository;
        $this->requestContext = $requestContext;
    }
    
    public function supports(string $dtoName): bool
    {
        return $dtoName === OrderSourceDto::class;
    }
    
    /**
     * @param OrderSourceDto $dto
     */
    public function createEntityFromDto(DtoInterface $dto, string $userId): OrderSource
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
        
        $currentOrderSources = $material->getOrderSources();
        $dto->priority = 1;
        /** @var OrderSource $currentOrderSource */
        foreach ($currentOrderSources as $currentOrderSource) {
            if ($currentOrderSource->getPriority() === 1) {
                $dto->priority = 2;
            }
        }
        
        $orderSource = $this->orderSourceService->addOrderSourceToMaterial(
            $dto, $material
        );
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $orderSource));
    
        return $orderSource;
    }
    
    /**
     * @param OrderSourceDto $dto
     * @param OrderSource $entity
     */
    public function putEntityFromDto(DtoInterface $dto, object $entity): OrderSource
    {
        $dto->id = $entity->getId();
        
        $this->validate($dto);
        
        if ($dto->priority === 1) {
            $this->orderSourceService->setLowPriorityOfMaterialOrderSources($entity->getMaterial());
        }
        $entity->setPriority($dto->priority);
    
        $entity->setOrderNumber($dto->orderNumber);
        $entity->setNote($dto->note);
        $entity->setAmountPerPurchaseUnit($dto->amountPerPurchaseUnit);
        if ($dto->price) {
            if ($dto->price !== $entity->getPrice()) {
                $entity->setLastPriceUpdate(new DateTimeImmutable());
            }
            $entity->setPrice($dto->price);
        } else {
            $entity->setPrice(null);
        }
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
        
        return $entity;
    }
    
    public function patchEntityFromDto(DtoInterface $dto, object $entity)
    {
        throw UnsupportedMethodException::forUnsupportedMethod('patchEntityFromDto');
    }
    
    /**
     * @param OrderSourceDto $dto
     */
    public function prepareImport(DtoInterface $dto, string $userId): ?OrderSourceDto
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        if ($dto->material->originalId) {
            $material = $this->materialRepository->findByAltScannerId($dto->material->originalId);
            if (!$material) {
                throw MissingDataException::forEntityNotFound($dto->material->originalId, Material::class);
            }
            $dto->material->id = $material->getId();
        }
        
        $supplier = $this->supplierRepository->findByName($dto->supplier->name);
        if (!$supplier) {
            $supplier = new Supplier($dto->supplier->name, $user->getCompany());
            $this->entityManager->persist($supplier);
        }
        $dto->supplier->id = $supplier->getId();
        
        $existingOrderSource = $this->orderSourceService->getOrderSourceOfMaterialIdAndSupplierName($dto->material->id, $dto->supplier->name);
        
        if ($existingOrderSource) {
            return null;
        }
        
        return $dto;
    }
}
