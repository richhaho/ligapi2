<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\GridStateDto;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\GridStateOwnerType;
use App\Entity\GridState;
use App\Event\ChangeEvent;
use App\Repository\GridStateRepository;
use App\Services\CurrentUserProvider;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class GridStateMapper
{
    use ValidationTrait;
    
    
    private CurrentUserProvider $currentUserProvider;
    private ValidatorInterface $validator;
    private EventDispatcherInterface $eventDispatcher;
    private GridStateRepository $gridStateRepository;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        GridStateRepository $gridStateRepository
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
        $this->gridStateRepository = $gridStateRepository;
    }
    
    private function setData(GridState $gridState, GridStateDto $gridStateDto): GridState
    {
        $gridState->setName($gridStateDto->name);
        $gridState->setColumnState($gridStateDto->columnState);
        $gridState->setFilterState($gridStateDto->filterState);
        $gridState->setSortState($gridStateDto->sortState);
        $gridState->setPaginationState($gridStateDto->paginationState);
        
        return $gridState;
    }
    
    public function createGridStateFromDto(GridStateDto $gridStateDto): GridState
    {
        $this->validate($gridStateDto);
        
        $company = $this->currentUserProvider->getCompany();
        
        if ($gridStateDto->isDefault) {
            $user = $this->currentUserProvider->getAuthenticatedUser();
            $existingGridState = $this->gridStateRepository->findDefaultByTypeAndUser($gridStateDto->gridType, $user);
        } else {
            $existingGridState = $this->gridStateRepository->findByTypeAndName($gridStateDto->gridType, $gridStateDto->name);
        }
        
        if ($existingGridState) {
            return $this->updateGridStateFromDto($existingGridState, $gridStateDto);
        }
        
        $gridStateOwnerType = GridStateOwnerType::company();
        
        $user = null;
        if ($gridStateDto->isDefault) {
            $user = $this->currentUserProvider->getAuthenticatedUser();
            $gridStateOwnerType = GridStateOwnerType::user();
        }
        
        $gridState = new GridState(
            $company,
            $gridStateDto->gridType,
            $gridStateOwnerType,
            $gridStateDto->isDefault,
            $user
        );
        
        $gridState = $this->setData($gridState, $gridStateDto);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $gridState));
        
        return $gridState;
    }
    
    public function updateGridStateFromDto(GridState $gridState, GridStateDto $gridStateDto): GridState
    {
        $gridState = $this->setData($gridState, $gridStateDto);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $gridState));
        
        return $gridState;
    }
}
