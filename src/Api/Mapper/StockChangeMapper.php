<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\MaterialLocationDto;
use App\Api\Dto\StockChangeDto;
use App\Entity\Data\ChangeAction;
use App\Entity\MaterialLocation;
use App\Entity\StockChange;
use App\Entity\User;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Exceptions\Domain\UnsupportedMethodException;
use App\Repository\MaterialLocationRepository;
use App\Repository\StockChangeRepository;
use App\Repository\UserRepository;
use App\Services\CurrentUserProvider;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class StockChangeMapper implements MapperInterface
{
    use ValidationTrait;
    
    private StockChangeRepository $stockChangeRepository;
    private MaterialLocationRepository $materialLocationRepository;
    private ValidatorInterface $validator;
    private CurrentUserProvider $currentUserProvider;
    private UserRepository $userRepository;
    private RequestContext $requestContext;
    private EventDispatcherInterface $eventDispatcher;
    
    public function __construct(
        StockChangeRepository $stockChangeRepository,
        MaterialLocationRepository $materialLocationRepository,
        ValidatorInterface $validator,
        CurrentUserProvider $currentUserProvider,
        UserRepository $userRepository,
        RequestContext $requestContext,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->stockChangeRepository = $stockChangeRepository;
        $this->materialLocationRepository = $materialLocationRepository;
        $this->validator = $validator;
        $this->currentUserProvider = $currentUserProvider;
        $this->userRepository = $userRepository;
        $this->requestContext = $requestContext;
        $this->eventDispatcher = $eventDispatcher;
    }
    
    public function supports(string $dtoName): bool
    {
        return $dtoName === StockChangeDto::class;
    }
    
    /**
     * @param StockChangeDto $dto
     */
    public function createEntityFromDto(DtoInterface $dto, string $userId): StockChange
    {
        $this->validate($dto);
    
        $company = $this->currentUserProvider->getCompany();
        
        $user = $this->currentUserProvider->getAuthenticatedUser();
        
        $materialLocation = $this->materialLocationRepository->find($dto->materialLocationDto->id);
        
        if (!$materialLocation) {
            throw MissingDataException::forEntityNotFound($dto->materialLocationDto->id, MaterialLocation::class);
        }
        
        if ($dto->user) {
            $user = $this->userRepository->find($dto->user->id);
            if (!$user) {
                throw MissingDataException::forEntityNotFound($dto->user->id, User::class);
            }
        }
        
        $stockChange = new StockChange(
            $company,
            $user,
            $dto->note,
            $materialLocation,
            $dto->amount,
            $dto->amountAlt,
            $dto->newCurrentStock,
            $dto->newCurrentStockAlt,
            null,
            $dto->originalId,
            $dto->createdAt
        );
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $stockChange));
        
        return $stockChange;
    }
    
    public function putEntityFromDto(DtoInterface $dto, object $entity)
    {
        throw UnsupportedMethodException::forUnsupportedMethod('putEntityFromDto');
    }
    
    public function patchEntityFromDto(DtoInterface $dto, object $entity)
    {
        throw UnsupportedMethodException::forUnsupportedMethod('patchEntityFromDto');
    }
    
    /**
     * @param StockChangeDto $dto
     */
    public function prepareImport(DtoInterface $dto, string $userId)
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $existingStockChange = $this->stockChangeRepository->findByOriginalId($dto->originalId);
        
        if ($existingStockChange) {
            return null;
        }
        
        $materialLocationDto = new MaterialLocationDto();
        $materialLocation = $this->materialLocationRepository->findByOriginalId($dto->materialLocationId);
        if (!$materialLocation) {
            throw MissingDataException::forEntityNotFound($dto->materialLocationId, MaterialLocation::class);
        }
        $materialLocationDto->id = $materialLocation->getId();
        $dto->materialLocationDto = $materialLocationDto;
        
        return $dto;
    }
}
