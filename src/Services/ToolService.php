<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\Tool;
use Doctrine\ORM\EntityManagerInterface;

class ToolService
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function deleteToolWithRelatedEntities(Tool $tool): void
    {
        foreach ($tool->getAllTasks() as $task) {
            $this->entityManager->remove($task);
        }
    
        foreach ($tool->getConsignmentItems() as $consignmentItem) {
            $this->entityManager->remove($consignmentItem);
        }
    
        foreach ($tool->getOwnerChanges() as $ownerChange) {
            $this->entityManager->remove($ownerChange);
        }
        
        $this->entityManager->remove($tool);
    }
}
