<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\ApiContext;
use App\Api\Dto\TaskDto;
use App\Api\Mapper\TaskMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Task;
use App\Event\ChangeEvent;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/tasks/", name="api_task_")
 */
class TaskController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_task_get")
     */
    public function index(TaskRepository $repository): iterable
    {
        return $repository->findAll();
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_task_get")
     */
    public function get(Task $task): Task
    {
        return $task;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_task_get")
     */
    public function create(
        TaskDto $createTask,
        TaskMapper $mapper,
        EntityManagerInterface $em
    ): Task
    {
        $task = $mapper->createTaskFromDto($createTask);
        $em->persist($task);
        $em->flush();
        
        return $task;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     * @ApiContext(groups={"detail"})
     */
    public function put(
        TaskDto $putTask,
        TaskMapper $mapper,
        Task $task,
        EntityManagerInterface $em,
        Security $security
    ) : Task
    {
        if(!$security->isGranted(Permission::EDIT, $task)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $task = $mapper->putTaskFromDto($putTask, $task);
        $em->flush();
        return $task;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        Task $task,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::DELETE, $task)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $entityManager->remove($task);
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $task));
        $entityManager->flush();
        
        return new Response(null, 204);
    }
}
