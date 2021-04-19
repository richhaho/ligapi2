<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\SupplierDto;
use App\Api\Mapper\SupplierMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Material;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use App\Event\ChangeEvent;
use App\Repository\SupplierRepository;
use App\Services\CurrentUserProvider;
use App\Services\SupplierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route(path="/api/suppliers/", name="api_supplier_")
 */
class SupplierController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_supplier_get")
     */
    public function index(SupplierRepository $repository, Security $security, EntityManagerInterface $entityManager): iterable
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $entityManager->getFilters()->enable('deleted');
        
        return $repository->findBy([], ['name' => 'ASC']);
    }
    /**
     * @Route(path="toorder", name="with_to_order", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_supplier_get")
     */
    public function with_to_order(SupplierRepository $repository, Security $security, EntityManagerInterface $entityManager): iterable
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $entityManager->getFilters()->enable('deleted');
        
        return $repository->findWithToOrder();
    }
    
    /**
     * @Route(path="names", name="get_names", methods={"GET"})
     */
    public function get_names(SupplierRepository $supplierRepository, Security $security, EntityManagerInterface $entityManager): array
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $entityManager->getFilters()->enable('deleted');
        
        return $supplierRepository->getNames();
    }
    
    /**
     * @Route(path="remaining/{id}", name="remaining_suppliers_for_material", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_supplier_get")
     */
    public function get_remaining_suppliers_for_material(Material $material, SupplierRepository $supplierRepository, Security $security, EntityManagerInterface $entityManager): iterable
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $entityManager->getFilters()->enable('deleted');
        
        $allSuppliers = $supplierRepository->findAll();
        $orderSources = $material->getOrderSources();
        
        $remainingSuppliers = [];
        /** @var Supplier $allSupplier */
        foreach ($allSuppliers as $allSupplier) {
            $supplierAlreadyAsigned = false;
            /** @var OrderSource $orderSource */
            foreach ($orderSources as $orderSource) {
                if ($orderSource->getSupplier() && $orderSource->getSupplier()->getId() === $allSupplier->getId()) {
                    $supplierAlreadyAsigned = true;
                }
            }
            if (!$supplierAlreadyAsigned) {
                $remainingSuppliers[] = $allSupplier;
            }
        }
        
        return $remainingSuppliers;
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_supplier_get")
     */
    public function get(Supplier $supplier, Security $security): Supplier
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $supplier;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_supplier_get")
     */
    public function create(
        SupplierDto $createSupplier,
        SupplierMapper $mapper,
        EntityManagerInterface $em,
        Security $security,
        CurrentUserProvider $currentUserProvider
    ): Supplier
    {
        if(!$security->isGranted(Permission::ADMIN, 'materials')) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $userId = $currentUserProvider->getAuthenticatedUser()->getId();
        $supplier = $mapper->createEntityFromDto($createSupplier, $userId);
        $em->persist($supplier);
        $em->flush();

        return $supplier;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     */
    public function put(
        SupplierDto $putSupplier,
        SupplierMapper $mapper,
        Supplier $supplier,
        EntityManagerInterface $entityManager,
        Security $security
    ): Supplier
    {
        if(!$security->isGranted(Permission::ADMIN, 'materials')) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $supplier = $mapper->putEntityFromDto($putSupplier, $supplier);
        $entityManager->flush();
        return $supplier;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        Supplier $supplier,
        SupplierService $supplierService,
        EntityManagerInterface $entityManager,
        Security $security,
        EventDispatcherInterface $eventDispatcher
    ): Response
    {
        if(!$security->isGranted(Permission::ADMIN, 'materials')) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $supplier->setDeleted(true);
        
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $supplier));
        $entityManager->flush();
        
        return new Response(null, 204);
    }
}
