<?php

declare(strict_types=1);


namespace App\Services\BatchChanges\Handler;


use App\Api\Dto\BatchUpdateDtoInterface;
use App\Api\Mapper\MapperWithBatchUpdateInterface;
use App\Exceptions\Domain\InvalidArgumentException;
use Doctrine\ORM\EntityManagerInterface;

class BatchUpdateHandler
{
    private EntityManagerInterface $entityManager;
    private iterable $mappersWithBatchUpdate;
    
    public function __construct(iterable $mappersWithBatchUpdate, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->mappersWithBatchUpdate = $mappersWithBatchUpdate;
    }
    
    public function __invoke(BatchUpdateDtoInterface $batchUpdateDto): iterable
    {
        /** @var MapperWithBatchUpdateInterface $mapper */
        foreach ($this->mappersWithBatchUpdate as $mapper) {
            if ($mapper->supports(get_class($batchUpdateDto))) {
                $entities = $mapper->batchUpdateFromDto($batchUpdateDto);
                $this->entityManager->flush();
                return $entities;
            }
        }
        
        throw InvalidArgumentException::forUnsupportedDto($batchUpdateDto);
    }
}
