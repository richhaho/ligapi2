<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\Company;
use App\Entity\Supplier;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SupplierService
{
    private array $storedSuppliers;
    private EventDispatcherInterface $eventDispatcher;
    private EntityManagerInterface $entityManager;
    private SupplierRepository $supplierRepository;
    
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        SupplierRepository $supplierRepository
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->supplierRepository = $supplierRepository;
        $this->storedSuppliers = [];
    }
    
//    public function removeRelatedEntitiesFromSupplier(Supplier $supplier)
//    {
//        /** @var OrderSource $orderSource */
//        foreach ($supplier->getOrderSources() as $orderSource) {
//            foreach ($orderSource->getMaterialOrderPositions() as $directOrderPosition) {
//                $this->entityManager->remove($directOrderPosition);
//            }
//            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $orderSource));
//            $this->entityManager->remove($orderSource);
//        }
//
//        /** @var MaterialOrder $directOrder */
//        foreach($supplier->getMaterialOrders() as $directOrder) {
//            foreach ($directOrder->getMaterialOrderPositions() as $directOrderPosition) {
//                $this->entityManager->remove($directOrderPosition);
//            }
//            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $directOrder));
//            $this->entityManager->remove($directOrder);
//        }
//
//        /** @var DirectOrder $directOrder */
//        foreach($supplier->getDirectOrders() as $directOrder) {
//            foreach ($directOrder->getDirectOrderPositions() as $directOrderPosition) {
//                $this->entityManager->remove($directOrderPosition);
//            }
//            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $directOrder));
//            $this->entityManager->remove($directOrder);
//        }
//
//        foreach($supplier->getAutoMaterials() as $crawlerMaterial) {
//            $this->entityManager->remove($crawlerMaterial);
//        }
//    }
    
    public function getSupplier(Company $company, ?string $name = null, ?string $id = null): ?Supplier
    {
        if (!$id && !$name) {
            return null;
        }
        
        if ($id) {
            $supplier = $this->supplierRepository->find($id);
            if (!$supplier) {
                throw MissingDataException::forEntityNotFound($id, Supplier::class);
            }
            $this->storedSuppliers[] = $supplier;
            return $supplier;
        }
    
        /** @var Supplier $storedSupplier */
        foreach ($this->storedSuppliers as $storedSupplier) {
            if ($storedSupplier->getName() === $name) {
                return $storedSupplier;
            }
        }
        
        $supplier = $this->supplierRepository->findByName($name);
        
        if ($supplier) {
            $this->storedSuppliers[] = $supplier;
            return $supplier;
        }
        
        $supplier = new Supplier($name, $company);
        $this->entityManager->persist($supplier);
        
        $this->storedSuppliers[] = $supplier;
        
        return $supplier;
    }
}
