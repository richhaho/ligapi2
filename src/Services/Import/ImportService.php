<?php

declare(strict_types=1);


namespace App\Services\Import;

use App\Api\Mapper\CommonMapper;
use App\Services\BatchChanges\Handler\CreateManyHandler;
use App\Services\BatchChanges\Messages\CreateMany;
use App\Services\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class ImportService
{
    const BATCHSIZE = 1;
    
    private CreateDtoFromFileService $createDtoFromFileService;
    private EntityManagerInterface $entityManager;
    private CommonMapper $commonMapper;
    private MessageBusInterface $messageBus;
    private CurrentUserProvider $currentUserProvider;
    private CreateManyHandler $createManyHandler;
    
    public function __construct(
        CreateDtoFromFileService $createDtoFromFileService,
        EntityManagerInterface $entityManager,
        CommonMapper $commonMapper,
        MessageBusInterface $messageBus,
        CurrentUserProvider $currentUserProvider,
        CreateManyHandler $createManyHandler
    )
    {
        $this->createDtoFromFileService = $createDtoFromFileService;
        $this->entityManager = $entityManager;
        $this->commonMapper = $commonMapper;
        $this->messageBus = $messageBus;
        $this->currentUserProvider = $currentUserProvider;
        $this->createManyHandler = $createManyHandler;
    }
    
    public function importFile(UploadedFile $file)
    {
        ini_set('memory_limit', '1024M');
    
        $currentCount = 0;
        $userId = $this->currentUserProvider->getAuthenticatedUser()->getId();
        
        $dtos = [];
        
        $dtoGenerator = $this->createDtoFromFileService->getDtoGeneratorFromFile($file);
        
        while ($dtoGenerator->current()) {
            $dtos[] = $dtoGenerator->current();
            $dtoGenerator->next();
            $currentCount++;
            if ($currentCount >= self::BATCHSIZE) {
                $createMany = new CreateMany($dtos, $userId);
//                $this->messageBus->dispatch($createMany);
                $this->createManyHandler->createEntities($createMany);
                $this->entityManager->flush();
                
                $filter = $this->entityManager->getFilters()->getFilter('company');
                $currentUser = $this->currentUserProvider->getAuthenticatedUser();
                if ($currentUser) {
                    $companyId = $this->currentUserProvider->getCompany()->getId();
                    $filter->setParameter('company_id', $companyId);
                }
                
                $currentCount = 0;
                $dtos = [];
            }
        }
        if (count($dtos) > 0) {
            $createMany = new CreateMany($dtos, $userId);
//            $this->messageBus->dispatch($createMany);
            $this->createManyHandler->createEntities($createMany);
            $this->entityManager->flush();
    
            $filter = $this->entityManager->getFilters()->getFilter('company');
            $currentUser = $this->currentUserProvider->getAuthenticatedUser();
            if ($currentUser) {
                $companyId = $this->currentUserProvider->getCompany()->getId();
                $filter->setParameter('company_id', $companyId);
            }
        }
    }
}
