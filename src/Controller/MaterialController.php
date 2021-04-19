<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\PdfDocumentDto;
use App\Api\Dto\ListQueryDto;
use App\Api\Dto\MaterialBatchUpdatesDto;
use App\Api\Dto\PatchManyDto;
use App\Api\Dto\PutMaterialDto;
use App\Api\Dto\MaterialLocationDto;
use App\Api\Dto\MaterialMultipleDto;
use App\Api\Dto\OrderSourceDto;
use App\Api\Dto\ManyDto;
use App\Api\Dto\QuickBookDto;
use App\Api\Dto\StocktakingDto;
use App\Api\Mapper\MaterialLocationMapper;
use App\Api\Mapper\OrderSourceMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\MaterialLocation;
use App\Entity\OrderSource;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\MaterialLocationRepository;
use App\Repository\OrderSourceRepository;
use App\Repository\StockChangeRepository;
use App\Services\BatchChanges\BatchChangesService;
use App\Services\BatchChanges\Handler\BatchUpdateHandler;
use App\Services\CurrentUserProvider;
use App\Services\Import\CreateDtoFromRequestService;
use App\Services\MaterialService;
use App\Services\Pdf\PdfService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Material;
use App\Repository\MaterialRepository;
use App\Api\Mapper\MaterialMapper;
use App\Api\Dto\CreateMaterialDto;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/materials/", name="api_material_")
 */
class MaterialController
{
    /**
     * @Route(path="labels", name="create_label", methods={"POST"})
     */
    public function create_label(
        PdfDocumentDto $labelDto,
        PdfService $pdfService,
        Security $security
    ): string
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $labelDto->entityType = 'material';
        
        return $pdfService->createDocumentFromPdfDocumentDtoAndEntityType($labelDto);
    }
    
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_material_get")
     */
    public function index(MaterialRepository $repository, Security $security, Request $request): iterable
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        ini_set('memory_limit', '1024M');
        
        $query = $request->query->all();
        
        if (isset($query['orderStatus']) && $query['orderStatus'] === 'onItsWay') {
            $apiContext = $request->attributes->get('_api_context');
            $apiContext->groups = ['orderedMaterials'];
            $request->attributes->set('_api_context', $apiContext);
        }
        
        return $repository->findAllActiveMaterials($query);
    }
    
    /**
     * @Route(path="archived", name="archived", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_material_get")
     */
    public function archived(MaterialRepository $repository, Security $security): iterable
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }

        ini_set('memory_limit', '1024M');

        return $repository->findAllArchivedMaterials();
    }
    
    /**
     * @Route(path="query", name="index_post", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_material_get")
     */
    public function index_post(MaterialRepository $repository, Security $security, ListQueryDto $listQueryDto): iterable
    {
        if(!$security->isGranted(Permission::CREATE, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        return [
            "rows" => $repository->filter($listQueryDto),
            "lastRow" => $repository->rowsCount($listQueryDto),
        ];
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail", "materialconsignmentdetails"}, selfRoute="api_material_get")
     */
    public function get(Material $material, Security $security): Material
    {
        if(!$security->isGranted(Permission::READ, $material)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $material;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_material_get")
     */
    public function create(
        CreateMaterialDto $createMaterial,
        CurrentUserProvider $currentUserProvider,
        MaterialMapper $mapper,
        EntityManagerInterface $em,
        RequestContext $requestContext,
        Security $security
    ): Material
    {
        if(!$security->isGranted(Permission::CREATE, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $requestContext->setParameter('changeSource', 'user');
        $material = $mapper->createEntityFromDto($createMaterial, $currentUserProvider->getAuthenticatedUser()->getId());
        $em->persist($material);
        $em->flush();
    
        return $material;
    }
    
    /**
     * @Route(path="multiple", name="create_multiple", methods={"POST"})
     * @ApiContext(groups={"detail"}, selfRoute="api_material_get")
     */
    public function create_multiple(
        MaterialMultipleDto $materialMultipleDto,
        MaterialMapper $mapper,
        MaterialRepository $materialRepository,
        EntityManagerInterface $em,
        RequestContext $requestContext,
        CurrentUserProvider $currentUserProvider,
        Security $security
    ): array
    {
        if(!$security->isGranted(Permission::CREATE, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $requestContext->setParameter('changeSource', 'user');
        
        $firstItemNumber = (int) ($materialRepository->findHighestItemNumber($currentUserProvider->getCompany()) + 1);
        
        $materialDto = $materialMultipleDto->material;
        
        $createdMaterials = [];
        
        for ($i = 0; $i < $materialMultipleDto->amount; $i++) {
            $materialDto->itemNumber = (string) ($firstItemNumber + $i);
            $material = $mapper->createEntityFromDto($materialMultipleDto->material, $currentUserProvider->getAuthenticatedUser()->getId());
            $em->persist($material);
            $createdMaterials[] = $material;
        }
        
        $em->flush();
        
        return $createdMaterials;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     * @ApiContext(groups={"detail"}, selfRoute="api_material_get")
     */
    public function put(
        PutMaterialDto $putMaterial,
        MaterialMapper $mapper,
        Material $material,
        EntityManagerInterface $em,
        RequestContext $requestContext,
        Security $security
    ) : Material
    {
        if(!$security->isGranted(Permission::EDIT, $material)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $requestContext->setParameter('changeSource', 'user');
    
//        $lock = $factory->createLock($material->getId(), 300, false);
//        $isAcquired = $lock->isAcquired();
//        if ($isAcquired) {
//            $test = 1;
//            // throw ...
//        }
//
//        $lock->acquire();
//
//        $isAcquired2 = $lock->isAcquired();
        
        $material->setUpdatedAt(new DateTimeImmutable()); // TODO: Can someone explain me, why the update works here but not, if I put the setUpdatedAt one line below the putEntityFromDto?
        $material = $mapper->putEntityFromDto($putMaterial, $material);
        $em->flush();
        
        return $material;
    }
    
    /**
     * @Route(path="{id}/archive", name="archive", methods={"GET"})
     */
    public function archive(Material $material, EntityManagerInterface $entityManager, Security $security): Response
    {
        if(!$security->isGranted(Permission::EDIT, $material)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $material->setIsArchived(true);
        $entityManager->flush();
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="{id}/activate", name="activate", methods={"GET"})
     */
    public function activate(Material $material, EntityManagerInterface $entityManager, Security $security): Response
    {
        if(!$security->isGranted(Permission::EDIT, $material)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $material->setIsArchived(false);
        $entityManager->flush();
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        Material $material,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::DELETE, $material)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $material->setDeleted(true);
//        $materialService->deleteMaterialWithRelatedEntities($material);
    
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $material));
        $entityManager->flush();
    
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="{id}/locations", name="api_material_locations_get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_materialLocation_get")
     */
    public function get_locations(string $id, MaterialLocationRepository $materialLocationRepository, Security $security)
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $materialLocationRepository->getMaterialLocationsOfMaterial($id);
    }
    
    /**
     * @Route(path="{materialId}/locations/{id}/stockchanges", name="api_material_location_stockchanges_get", methods={"GET"})
     * @ApiContext(groups={"listStockChanges"}, selfRoute="api_materialLocation_get")
     */
    public function get_location_stockchanges(MaterialLocation $materialLocation, Security $security)
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $materialLocation->getStockChanges();
    }
    
    /**
     * @Route(path="{id}/locations", name="add_location", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_materialLocation_get")
     */
    public function add_location(
        Material $material,
        MaterialLocationDto $createMaterialLocation,
        MaterialLocationMapper $mapper,
        CurrentUserProvider $currentUserProvider,
        EntityManagerInterface $entityManager,
        Security $security
    ): MaterialLocation
    {
        if(!$security->isGranted(Permission::EDIT, $material)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $materialLocation = $mapper->createEntityFromDto($createMaterialLocation, $currentUserProvider->getAuthenticatedUser()->getId());
        $entityManager->persist($materialLocation);
        $entityManager->flush();
    
        return $materialLocation;
    }
    
    /**
     * @Route(path="{materialId}/locations/{id}/stock", name="put_location_stock", methods={"PUT"})
     */
    public function put_location_stock(
        StocktakingDto $stocktakingDto,
        MaterialLocationMapper $mapper,
        MaterialLocation $materialLocation,
        EntityManagerInterface $entityManager,
        Security $security
    ): MaterialLocation
    {
        if(!$security->isGranted(Permission::BOOK, $materialLocation->getMaterial())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $materialLocation = $mapper->updateMaterialLocationStock($stocktakingDto, $materialLocation);
        $entityManager->flush();
        
        return $materialLocation;
    }
    
    /**
     * @Route(path="{materialId}/locations/{id}", name="put_location", methods={"PUT"})
     * @ApiContext(groups={"stocktaking"}, selfRoute="api_materialLocation_get")
     */
    public function put_location(
        MaterialLocationDto $materialLocationDto,
        MaterialLocationMapper $mapper,
        MaterialLocation $materialLocation,
        EntityManagerInterface $entityManager,
        Security $security
    ): MaterialLocation
    {
        if(!$security->isGranted(Permission::BOOK, $materialLocation->getMaterial())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $materialLocation = $mapper->putEntityFromDto($materialLocationDto, $materialLocation);
        $entityManager->flush();
    
        return $materialLocation;
    }
    
    /**
     * @Route(path="{materialId}/locations/{id}", name="delete_location", methods={"DELETE"})
     * @ApiContext(groups={"detail"}, selfRoute="api_material_get")
     */
    public function delete_location(
        MaterialLocation $materialLocation,
        EntityManagerInterface $entityManager,
        Security $security,
        EventDispatcherInterface $eventDispatcher
    ): Material
    {
        if(!$security->isGranted(Permission::EDIT, $materialLocation->getMaterial())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $material = $materialLocation->getMaterial();
    
        foreach ($materialLocation->getStockChanges() as $stockChange) {
            $entityManager->remove($stockChange);
        }
    
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $materialLocation));
        
        $entityManager->remove($materialLocation);
        $entityManager->flush();
    
        return $material;
    }
    
    /**
     * @Route(path="{id}/ordersources", name="api_material_ordersources_get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_ordersource_get")
     */
    public function get_ordersources(Material $material, OrderSourceRepository $orderSourceRepository, Security $security)
    {
        if(!$security->isGranted(Permission::READ, $material)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $orderSourceRepository->getOrderSourcesOfMaterial($material);
    }
    
    /**
     * @Route(path="{id}/ordersources", name="add_ordersource", methods={"POST"})
     */
    public function add_ordersource(
        Material $material,
        OrderSourceDto $orderSourceDto,
        OrderSourceMapper $mapper,
        EntityManagerInterface $entityManager,
        RequestContext $requestContext,
        Security $security,
        CurrentUserProvider $currentUserProvider
    ): Response
    {
        if(!$security->isGranted(Permission::EDIT, $material)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $userId = $currentUserProvider->getAuthenticatedUser()->getId();
        $requestContext->setParameter('changeSource', 'user');
        $materialDto = new PutMaterialDto();
        $materialDto->id = $material->getId();
        $orderSourceDto->material = $materialDto;
        $orderSource = $mapper->createEntityFromDto($orderSourceDto, $userId);
        $entityManager->persist($orderSource);
        $entityManager->flush();
    
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="{materialId}/ordersources/{id}", name="put_ordersource", methods={"PUT"})
     * @ApiContext(groups={"detail"}, selfRoute="api_ordersource_get")
     */
    public function put_ordersource(
        OrderSourceDto $orderSourceDto,
        OrderSourceMapper $mapper,
        OrderSource $orderSource,
        EntityManagerInterface $entityManager,
        RequestContext $requestContext,
        Security $security
    ): OrderSource
    {
        if(!$security->isGranted(Permission::EDIT, $orderSource->getMaterial())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $requestContext->setParameter('changeSource', 'user');
        $orderSource = $mapper->putEntityFromDto($orderSourceDto, $orderSource);
        $entityManager->flush();
    
        return $orderSource;
    }
    
    /**
     * @Route(path="{materialId}/ordersources/{id}", name="delete_ordersource", methods={"DELETE"})
     * @ApiContext(groups={"detail"})
     */
    public function delete_ordersource(
        OrderSource $orderSource,
        EntityManagerInterface $entityManager,
        Security $security,
        EventDispatcherInterface $eventDispatcher
    ): Material
    {
        if(!$security->isGranted(Permission::EDIT, $orderSource->getMaterial())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $material = $orderSource->getMaterial();
    
        foreach ($orderSource->getMaterialOrderPositions() as $orderPosition) {
            $entityManager->remove($orderPosition);
        }
    
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $orderSource));
        $entityManager->remove($orderSource);
        
        $entityManager->flush();
        return $material;
    }
    
    /**
     * @Route(path="{id}/stockchanges", name="api_stockchanges_get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_stockchange_get")
     */
    public function get_stockchanges(Material $material, StockChangeRepository $stockChangeRepository, Security $security)
    {
        if(!$security->isGranted(Permission::READ, $material)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $stockChangeRepository->getStockChangesOfMaterial($material);
    }
    
    /**
     * @Route(path="{materialid}/stockchanges/{id}", name="api_stockchange_get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_stockchange_get")
     */
    public function get_stockchange(string $material, string $id, StockChangeRepository $stockChangeRepository, Security $security)
    {
        if(!$security->isGranted(Permission::READ, $material)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $stockChangeRepository->find($id);
    }
    
    /**
     * @Route(path="many", name="patch_many", methods={"PATCH"})
     */
    public function patch_many(
        ManyDto $patchManyDto,
        BatchChangesService $batchChangesService,
        CurrentUserProvider $currentUserProvider,
        EntityManagerInterface $entityManager,
        RequestContext $requestContext,
        MaterialMapper $materialMapper,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::EDIT, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $changeCount = count($patchManyDto->ids);
        $user = $currentUserProvider->getAuthenticatedUser();
    
        $requestContext->setParameter('changeSource', 'user');
        
        if ($changeCount > 50) {
            $batchChangesService->createBatchChangesMessengesForPatchMany($patchManyDto, $user);
            return new Response('Changes are being processed in the background', 200);
        } else {
            $materialMapper->patchManyFromDto($patchManyDto, $user->getId());
            $entityManager->flush();
            return new Response(null, 204);
        }
    }
    
    /**
     * @Route(path="manyrequest", name="patch_many_from_request", methods={"POST"})
     */
    public function patch_many_individual_requests(
        PatchManyDto $request,
        CreateDtoFromRequestService $createDtoFromRequestService,
        EntityManagerInterface $entityManager,
        RequestContext $requestContext,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::EDIT, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $requestContext->setParameter('changeSource', 'user');
        
        $createDtoFromRequestService->patchEntities($request->data, Material::class, PutMaterialDto::class);
    
        $entityManager->flush();
    
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="deletemany", name="delete_many", methods={"POST"})
     */
    public function delete_many(
        ManyDto $manyDto,
        MaterialRepository $materialRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response
    {
        foreach ($manyDto->ids as $id) {
            $material = $materialRepository->find($id);
            if (!$material) {
                throw MissingDataException::forEntityNotFound($id, Material::PERMISSION);
            }
            if(!$security->isGranted(Permission::DELETE, $material)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
            $material->setDeleted(true);
            
            $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $material));
            
            $entityManager->flush();
        }
    
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="batchupdate", name="batch_update", methods={"POST"})
     * @ApiContext(groups={"detail"})
     */
    public function batch_update(
        MaterialBatchUpdatesDto $materialBatchUpdatesDto,
        Security $security,
        RequestContext $requestContext,
        BatchUpdateHandler $batchUpdateHandler,
        MaterialRepository $materialRepository
    ): iterable
    {
        foreach ($materialBatchUpdatesDto->materialBatchUpdates as $batchUpdateDto) {
            $material = $materialRepository->find($batchUpdateDto->id);
            if (!$material) {
                throw MissingDataException::forEntityNotFound($batchUpdateDto->id, Material::class);
            }
            if(!$security->isGranted(Permission::EDIT, $material)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
        }
    
        $requestContext->setParameter('changeSource', 'user');
        
        return $batchUpdateHandler($materialBatchUpdatesDto);
    }
    
    /**
     * @Route(path="copy", name="copy", methods={"POST"})
     * @ApiContext(groups={"detail"})
     */
    public function copy(
        ManyDto $manyDto,
        Security $security,
        MaterialRepository $materialRepository,
        EntityManagerInterface $entityManager,
        MaterialMapper $materialMapper
    ): iterable
    {
        $createdMaterials = [];
        foreach ($manyDto->ids as $id) {
            $material = $materialRepository->find($id);
            if (!$material) {
                throw MissingDataException::forEntityNotFound($id, Material::PERMISSION);
            }
            if(!$security->isGranted(Permission::CREATE, Material::PERMISSION)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
    
            $createdMaterials[] = $materialMapper->createMaterialFromMaterial($material);
            
            $entityManager->flush();
        }
    
        return $createdMaterials;
    }
    
    /**
     * @Route(path="quickbook", name="quickbook", methods={"POST"})
     * @ApiContext(groups={"detail"})
     */
    public function quickbook(
        QuickBookDto $quickBookDto,
        Security $security,
        MaterialService $materialService,
        EntityManagerInterface $entityManager
    ): string
    {
        if(!$security->isGranted(Permission::BOOK, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $result = $materialService->quickBook($quickBookDto);
        $entityManager->flush();
        return $result;
    }
}
