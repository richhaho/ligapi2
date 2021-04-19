<?php

declare(strict_types=1);


namespace App\Services\BatchChanges\Handler;


use App\Api\Dto\ManyDto;
use App\Api\Mapper\MaterialMapper;
use App\Services\BatchChanges\Messages\PatchMany;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PatchManyHandler implements MessageHandlerInterface
{
    
    private MaterialMapper $materialMapper;
    private EntityManagerInterface $entityManager;
    
    public function __construct(MaterialMapper $materialMapper, EntityManagerInterface $entityManager)
    {
        $this->materialMapper = $materialMapper;
        $this->entityManager = $entityManager;
    }
    
    public function __invoke(PatchMany $patchMany)
    {
        $dto = new ManyDto();
        
        $dto->query = $patchMany->getQuery();
        $dto->ids = $patchMany->getIds();
        
        $this->materialMapper->patchManyFromDto($dto, $patchMany->getUserId());
    
        $this->entityManager->flush();
    }
}
