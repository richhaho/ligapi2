<?php

declare(strict_types=1);


namespace App\Services\BatchChanges\Handler;

use App\Api\Mapper\CommonMapper;
use App\Exceptions\Domain\InconsistentDataException;
use App\Repository\UserRepository;
use App\Services\BatchChanges\Messages\CreateMany;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreateManyHandler implements MessageHandlerInterface
{
    private CommonMapper $commonMapper;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    
    public function __construct(CommonMapper $commonMapper, EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->commonMapper = $commonMapper;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }
    
    public function createEntities(CreateMany $createMany)
    {
        $this($createMany);
    }
    
    public function __invoke(CreateMany $createMany)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '600');
        
        $currentUser = $this->userRepository->find($createMany->getUserId());
        
        foreach ($createMany->getDtos() as $dto) {
            $dto = $this->commonMapper->prepareImport($dto, $currentUser->getId());
            if (!$dto) {
                continue;
            }
            $entity = $this->commonMapper->createEntityFromDto($dto, $createMany->getUserId());
            if ($entity) {
                $this->entityManager->persist($entity);
            }
            $this->entityManager->flush();
            $filter = $this->entityManager->getFilters()->getFilter('company');
            if ($currentUser) {
                $companyId = $currentUser->getCompany()->getId();
                $filter->setParameter('company_id', $companyId);
            } else {
                throw InconsistentDataException::forDataIsMissing('User Id');
            }
        }
    }
}
