<?php

declare(strict_types=1);


namespace App\Services\Import;


use App\Api\Mapper\CommonMapper;
use App\Entity\Data\ChangeAction;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateDtoFromRequestService
{
    private ManagerRegistry $managerRegistry;
    private DtoService $dtoService;
    private CommonMapper $commonMapper;
    private EventDispatcherInterface $eventDispatcher;
    
    public function __construct(
        ManagerRegistry $managerRegistry,
        DtoService $dtoService,
        CommonMapper $commonMapper,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->managerRegistry = $managerRegistry;
        $this->dtoService = $dtoService;
        $this->commonMapper = $commonMapper;
        $this->eventDispatcher = $eventDispatcher;
    }
    
    public function patchEntities(array $requests, string $entityClass, string $dtoClass): array
    {
        $resultItems = [];
        foreach ($requests as $request) {
            if (!isset($request['id'])) {
                continue;
            }
    
            /** @var EntityRepository $repository */
            $repository = $this->managerRegistry->getRepository($entityClass);
            
            $entity = $repository->find($request['id']);
            if (!$entity) {
                throw MissingDataException::forEntityNotFound($request['id'], $entityClass);
            }
            
            $dto = $this->dtoService->createDtoFromArray($request, $dtoClass);
            
            $entity = $this->commonMapper->patchEntityFromDto($entity, $dto);
            
            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
            
            $resultItems[] = $entity;
        }
        return $resultItems;
    }
}
