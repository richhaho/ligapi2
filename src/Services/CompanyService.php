<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;

class CompanyService
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }
    
    public function deleteCompany(
        Company $company
    ): void
    {
        foreach ($company->getMaterials() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getLocations() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getKeyys() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getTools() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getUsers() as $item) {
            $item->setSelectedMaterialLabelType(null);
            $item->setSelectedToolLabelType(null);
            $item->setSelectedKeyyLabelType(null);
//            $this->entityManager->remove($item);
        }
        
        foreach ($company->getSuppliers() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getItemGroups() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getTasks() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getSearchIndexes() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getOrderSources() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getPermissionGroups() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getStockChanges() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getProjects() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getMaterialOrders() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getCustomers() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getConsignments() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getConsignmentItems() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getCustomFields() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getDirectOrders() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getDirectOrderPositions() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getDirectOrderPositionResults() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getGridStates() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getLabelSpecifications() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getMaterialLocations() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getMaterialOrderPositions() as $item) {
            $this->entityManager->remove($item);
        }
        
        foreach ($company->getOwnerChanges() as $item) {
            $this->entityManager->remove($item);
        }
        
//        $this->entityManager->remove($company);
    }
}
