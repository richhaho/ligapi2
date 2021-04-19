<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\PdfDocumentDto;
use App\Api\Dto\ManyDto;
use App\Api\Dto\PutTool;
use App\Api\Dto\ToolBatchUpdatesDto;
use App\Api\Dto\ToolMultipleDto;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\ToolRepository;
use App\Services\BatchChanges\Handler\BatchUpdateHandler;
use App\Services\CurrentUserProvider;
use App\Services\Pdf\PdfService;
use App\Services\ToolService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Tool;
use App\Api\Mapper\ToolMapper;
use App\Api\Dto\CreateTool;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/tools/", name="api_tool_")
 */
class ToolController
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
        if(!$security->isGranted(Permission::READ, Tool::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $labelDto->entityType = 'tool';
    
        return $pdfService->createDocumentFromPdfDocumentDtoAndEntityType($labelDto);
    }
    
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_tool_get")
     */
    public function index(ToolRepository $repository, Security $security, Request $request, EntityManagerInterface $entityManager): iterable
    {
        if(!$security->isGranted(Permission::READ, Tool::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $entityManager->getFilters()->enable('deleted');
        
        return $repository->findAllActiveTools($request->query->all());
    }
    
    /**
     * @Route(path="archived", name="archived", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_tool_get")
     */
    public function archived(Request $request, ToolRepository $repository, Security $security): iterable
    {
        if(!$security->isGranted(Permission::READ, Tool::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        ini_set('memory_limit', '1024M');
        
        return $repository->findAllArchivedTools($request->query->all());
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_tool_get")
     */
    public function get(Tool $tool, Security $security): Tool
    {
        if(!$security->isGranted(Permission::READ, $tool)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $tool;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_tool_get")
     */
    public function create(
        CreateTool $createTool,
        ToolMapper $mapper,
        EntityManagerInterface $em,
        Security $security,
        CurrentUserProvider $currentUserProvider
    ): Tool
    {
        if(!$security->isGranted(Permission::CREATE, Tool::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $userId = $currentUserProvider->getAuthenticatedUser()->getId();
        $tool = $mapper->createEntityFromDto($createTool, $userId);
        $em->persist($tool);
        $em->flush();
    
        return $tool;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     */
    public function put(
        PutTool $putTool,
        ToolMapper $mapper,
        Tool $tool,
        EntityManagerInterface $em
    ) : Tool
    {
        // Security is handled in UpdateLogGenerator
        $tool->setUpdatedAt(new DateTimeImmutable());
        $tool = $mapper->putEntityFromDto($putTool, $tool);
        $em->flush();
        
        return $tool;
    }
    
    /**
     * @Route(path="{id}/archive", name="archive", methods={"GET"})
     */
    public function archive(
        Tool $tool,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::EDIT, $tool)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $tool->setIsArchived(true);
        $entityManager->flush();
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="{id}/activate", name="activate", methods={"GET"})
     */
    public function activate(
        Tool $tool,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::EDIT, $tool)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $tool->setIsArchived(false);
        $entityManager->flush();
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        Tool $tool,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::DELETE, $tool)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $tool->setDeleted(true);
        
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $tool));
        
        $entityManager->flush();
    
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="many", name="patch_many", methods={"PATCH"})
     */
    public function patch_many(ManyDto $patchManyDto, ToolMapper $toolMapper, EntityManagerInterface $entityManager, Security $security): Response
    {
        if(!$security->isGranted(Permission::EDIT, Tool::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $toolMapper->patchManyFromDto($patchManyDto);
        
        $entityManager->flush();
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="batchupdate", name="batch_update", methods={"POST"})
     * @ApiContext(groups={"detail"})
     */
    public function batch_update(
        ToolBatchUpdatesDto $toolBatchUpdatesDto,
        Security $security,
        RequestContext $requestContext,
        BatchUpdateHandler $batchUpdateHandler,
        ToolRepository $toolRepository
    ): iterable
    {
        foreach ($toolBatchUpdatesDto->toolBatchUpdates as $batchUpdateDto) {
            $tool = $toolRepository->find($batchUpdateDto->id);
            if (!$tool) {
                throw MissingDataException::forEntityNotFound($batchUpdateDto->id, Tool::class);
            }
            if(!$security->isGranted(Permission::EDIT, $tool)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
        }
        
        $requestContext->setParameter('changeSource', 'user');
        
        return $batchUpdateHandler($toolBatchUpdatesDto);
    }
    
    /**
     * @Route(path="deletemany", name="delete_many", methods={"POST"})
     */
    public function delete_many(
        ManyDto $manyDto,
        ToolRepository $toolRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        ToolService $toolService,
        Security $security
    ): Response
    {
        foreach ($manyDto->ids as $id) {
            $tool = $toolRepository->find($id);
            if (!$tool) {
                throw MissingDataException::forEntityNotFound($id, Tool::class);
            }
    
            if(!$security->isGranted(Permission::DELETE, $tool)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
    
            $toolService->deleteToolWithRelatedEntities($tool);
            
            $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $tool));
            
            $entityManager->flush();
        }
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="multiple", name="create_multiple", methods={"POST"})
     * @ApiContext(groups={"detail"}, selfRoute="api_tool_get")
     */
    public function create_multiple(
        ToolMultipleDto $toolMultipleDto,
        ToolMapper $mapper,
        ToolRepository $materialRepository,
        EntityManagerInterface $em,
        RequestContext $requestContext,
        CurrentUserProvider $currentUserProvider,
        Security $security
    ): array
    {
        if(!$security->isGranted(Permission::CREATE, Tool::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $requestContext->setParameter('changeSource', 'user');
        
        $firstItemNumber = (int) ($materialRepository->findHighestItemNumber($currentUserProvider->getCompany()) + 1);
        
        $toolDto = $toolMultipleDto->tool;
        
        $createdTools = [];
        
        for ($i = 0; $i < $toolMultipleDto->amount; $i++) {
            $toolDto->itemNumber = (string) ($firstItemNumber + $i);
            $tool = $mapper->createEntityFromDto($toolMultipleDto->tool, $currentUserProvider->getAuthenticatedUser()->getId());
            $em->persist($tool);
            $createdTools[] = $tool;
        }
        
        $em->flush();
        
        return $createdTools;
    }
}
