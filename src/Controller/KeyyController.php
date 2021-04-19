<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\KeyyBatchUpdatesDto;
use App\Api\Dto\KeyyMultipleDto;
use App\Api\Dto\ManyDto;
use App\Api\Dto\PdfDocumentDto;
use App\Api\Dto\CreateKeyy;
use App\Api\Dto\PutKeyy;
use App\Api\Mapper\KeyyMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Keyy;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\KeyyRepository;
use App\Services\BatchChanges\Handler\BatchUpdateHandler;
use App\Services\CurrentUserProvider;
use App\Services\KeyyService;
use App\Services\Pdf\PdfService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/keyys/", name="api_keyy_")
 */
class KeyyController
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
        if(!$security->isGranted(Permission::READ, Keyy::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $labelDto->entityType = 'keyy';
    
        return $pdfService->createDocumentFromPdfDocumentDtoAndEntityType($labelDto);
    }
    
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_keyy_get")
     */
    public function index(KeyyRepository $repository, Security $security, EntityManagerInterface $entityManager): iterable
    {
        if(!$security->isGranted(Permission::READ, Keyy::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $entityManager->getFilters()->enable('deleted');
        
        return $repository->findAllActiveKeyys();
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_keyy_get")
     */
    public function get(Keyy $keyy, Security $security): Keyy
    {
        if(!$security->isGranted(Permission::READ, $keyy)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $keyy;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_keyy_get")
     */
    public function create(
        CreateKeyy $createKeyy,
        KeyyMapper $mapper,
        EntityManagerInterface $em,
        Security $security,
        CurrentUserProvider $currentUserProvider
    ): Keyy
    {
        if(!$security->isGranted(Permission::CREATE, Keyy::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $userId = $currentUserProvider->getAuthenticatedUser()->getId();
        $keyy = $mapper->createEntityFromDto($createKeyy, $userId);
        $em->persist($keyy);
        $em->flush();

        return $keyy;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     * @ApiContext(groups={"detail"}, selfRoute="api_keyy_get")
     */
    public function put(
        PutKeyy $putKeyy,
        KeyyMapper $mapper,
        Keyy $keyy,
        EntityManagerInterface $em
    ) : Keyy
    {
        // Security is handled in UpdateLogGenerator
        $keyy->setUpdatedAt(new DateTimeImmutable());
        $keyy = $mapper->putEntityFromDto($putKeyy, $keyy);
        $em->flush();
        return $keyy;
    }
    
    /**
     * @Route(path="{id}/archive", name="archive", methods={"GET"})
     */
    public function archive(Keyy $keyy, EntityManagerInterface $entityManager, Security $security): Response
    {
        if(!$security->isGranted(Permission::EDIT, $keyy)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $keyy->setIsArchived(true);
        $entityManager->flush();
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="{id}/activate", name="activate", methods={"GET"})
     */
    public function activate(
        Keyy $keyy,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::EDIT, $keyy)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $keyy->setIsArchived(false);
        $entityManager->flush();
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        Keyy $keyy,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::DELETE, $keyy)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $keyy->setDeleted(true);
        
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $keyy));
        
        $entityManager->flush();
    
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="deletemany", name="delete_many", methods={"POST"})
     */
    public function delete_many(
        ManyDto $manyDto,
        KeyyRepository $keyyRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        KeyyService $keyyService,
        Security $security
    ): Response
    {
        foreach ($manyDto->ids as $id) {
            $key = $keyyRepository->find($id);
            if (!$key) {
                throw MissingDataException::forEntityNotFound($id, Keyy::class);
            }
            
            if(!$security->isGranted(Permission::DELETE, $key)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
            
            $keyyService->deleteKeyyWithRelatedEntities($key);
            
            $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $key));
            
            $entityManager->flush();
        }
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="multiple", name="create_multiple", methods={"POST"})
     * @ApiContext(groups={"detail"}, selfRoute="api_keyy_get")
     */
    public function create_multiple(
        KeyyMultipleDto $keyyMultipleDto,
        KeyyMapper $mapper,
        KeyyRepository $materialRepository,
        EntityManagerInterface $em,
        RequestContext $requestContext,
        CurrentUserProvider $currentUserProvider,
        Security $security
    ): array
    {
        if(!$security->isGranted(Permission::CREATE, Keyy::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $requestContext->setParameter('changeSource', 'user');
        
        $firstItemNumber = (int) ($materialRepository->findHighestItemNumber($currentUserProvider->getCompany()) + 1);
        
        $keyyDto = $keyyMultipleDto->keyy;
        
        $createdKeyys = [];
        
        for ($i = 0; $i < $keyyMultipleDto->amount; $i++) {
            $keyyDto->itemNumber = (string) ($firstItemNumber + $i);
            $keyy = $mapper->createEntityFromDto($keyyMultipleDto->keyy, $currentUserProvider->getAuthenticatedUser()->getId());
            $em->persist($keyy);
            $createdKeyys[] = $keyy;
        }
        
        $em->flush();
        
        return $createdKeyys;
    }
    
    /**
     * @Route(path="batchupdate", name="batch_update", methods={"POST"})
     * @ApiContext(groups={"detail"})
     */
    public function batch_update(
        KeyyBatchUpdatesDto $keyyBatchUpdatesDto,
        Security $security,
        RequestContext $requestContext,
        BatchUpdateHandler $batchUpdateHandler,
        KeyyRepository $keyyRepository
    ): iterable
    {
        foreach ($keyyBatchUpdatesDto->keyyBatchUpdates as $batchUpdateDto) {
            $material = $keyyRepository->find($batchUpdateDto->id);
            if (!$material) {
                throw MissingDataException::forEntityNotFound($batchUpdateDto->id, Keyy::class);
            }
            if(!$security->isGranted(Permission::EDIT, $material)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
        }
        
        $requestContext->setParameter('changeSource', 'user');
        
        return $batchUpdateHandler($keyyBatchUpdatesDto);
    }
}
