<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\ApiContext;
use App\Api\Dto\ProjectDto;
use App\Api\Mapper\ProjectMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Project;
use App\Event\ChangeEvent;
use App\Repository\ProjectRepository;
use App\Services\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/projects/", name="api_project_")
 */
class ProjectController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_project_get")
     */
    public function index(ProjectRepository $repository, Security $security, EntityManagerInterface $entityManager): iterable
    {
        if(!$security->isGranted(Permission::READ, Project::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $entityManager->getFilters()->enable('deleted');
        
        return $repository->findBy([], ['name' => 'ASC']);
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_project_get")
     */
    public function get(Project $project, Security $security): Project
    {
        if(!$security->isGranted(Permission::READ, $project)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $project;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_project_get")
     */
    public function create(
        ProjectDto $createProject,
        ProjectMapper $mapper,
        EntityManagerInterface $em,
        Security $security,
        CurrentUserProvider $currentUserProvider
    ): Project
    {
        if(!$security->isGranted(Permission::CREATE, Project::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $userId = $currentUserProvider->getAuthenticatedUser()->getId();
        $project = $mapper->createEntityFromDto($createProject, $userId);
        $em->persist($project);
        $em->flush();
        
        return $project;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     * @ApiContext(groups={"list"}, selfRoute="api_project_get")
     */
    public function put(
        ProjectDto $projectDto,
        ProjectMapper $mapper,
        Project $project,
        EntityManagerInterface $em,
        Security $security
    ) : Project
    {
        if(!$security->isGranted(Permission::EDIT, Project::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $project = $mapper->putEntityFromDto($projectDto, $project);
        $em->flush();
        return $project;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        Project $project,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::DELETE, Project::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $project->setDeleted(true);
    
//        foreach ($project->getConsignments() as $consignment) {
//            $entityManager->remove($consignment);
//        }
//
//        if ($project->getStockChanges()->count() > 0) {
//            throw InconsistentDataException::forProjectHasStockChanges($project);
//        }
//
//        $entityManager->remove($project);
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $project));
        $entityManager->flush();
        
        return new Response(null, 204);
    }
}
