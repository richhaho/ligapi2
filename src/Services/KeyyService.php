<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\Keyy;
use Doctrine\ORM\EntityManagerInterface;

class KeyyService
{
    
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function deleteKeyyWithRelatedEntities(Keyy $keyy)
    {
        foreach ($keyy->getAllTasks() as $task) {
            $this->entityManager->remove($task);
        }
    
        foreach ($keyy->getConsignmentItems() as $consignmentItem) {
            $this->entityManager->remove($consignmentItem);
        }
        
        $this->entityManager->remove($keyy);
    }
}
