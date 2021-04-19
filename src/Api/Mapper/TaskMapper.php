<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\TaskDto;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\TaskStatus;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\Task;
use App\Entity\Tool;
use App\Entity\User;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\KeyyRepository;
use App\Repository\MaterialRepository;
use App\Repository\ToolRepository;
use App\Repository\UserRepository;
use App\Services\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TaskMapper
{
    use ValidationTrait;
    
    private MaterialRepository $materialRepository;
    private ToolRepository $toolRepository;
    private KeyyRepository $keyyRepository;
    private EntityManagerInterface $entityManager;
    private CurrentUserProvider $currentUserProvider;
    private EventDispatcherInterface $eventDispatcher;
    private ValidatorInterface $validator;
    private UserRepository $userRepository;
    
    public function __construct(
        MaterialRepository $materialRepository,
        ToolRepository $toolRepository,
        KeyyRepository $keyyRepository,
        EntityManagerInterface $entityManager,
        CurrentUserProvider $currentUserProvider,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        UserRepository $userRepository
    )
    {
        $this->materialRepository = $materialRepository;
        $this->toolRepository = $toolRepository;
        $this->keyyRepository = $keyyRepository;
        $this->entityManager = $entityManager;
        $this->currentUserProvider = $currentUserProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
    }
    
    private function setTaskData(TaskDto $taskDto, Task $task): Task
    {
        
        $task->setDetails($taskDto->details);
        $task->setStartDateFromString($taskDto->startDate);
        $task->setDueDateFromString($taskDto->dueDate);
        $task->setPriority($taskDto->priority);
        
        if ($task->getDueDate() && $task->getStartDate() && $task->getStartDate() > $task->getDueDate()) {
            throw InvalidArgumentException::forInvalidTaskDates($task->getStartDate(), $task->getDueDate(), $task->getId());
        }
        
        return $task;
    }
    
    public function createTaskFromDto(TaskDto $taskDto): Task
    {
    
        $this->validate($taskDto);
        
        $currentUser = $this->currentUserProvider->getAuthenticatedUser();
    
        $material = null;
        if ($taskDto->materialId) {
            /** @var Material $material */
            $material = $this->materialRepository->find($taskDto->materialId);
            if (!$material) {
                throw MissingDataException::forEntityNotFound($taskDto->materialId, Material::class);
            }
        }
    
        $tool = null;
        if ($taskDto->toolId) {
            /** @var Tool $tool */
            $tool = $this->toolRepository->find($taskDto->toolId);
            if (!$tool) {
                throw MissingDataException::forEntityNotFound($taskDto->toolId, Tool::class);
            }
        }
    
        $keyy = null;
        if ($taskDto->keyyId) {
            /** @var Keyy $keyy */
            $keyy = $this->keyyRepository->find($taskDto->keyyId);
            if (!$keyy) {
                throw MissingDataException::forEntityNotFound($taskDto->keyyId, Keyy::class);
            }
        }
        
        $responsible = $this->userRepository->findByFullName($taskDto->responsible);
        if (!$responsible) {
            throw MissingDataException::forEntityNotFound($taskDto->responsible, User::class);
        }
        
        $task = new Task($currentUser->getCompany(), $taskDto->topic, $responsible, $material, $tool, $keyy);
        
        $this->setTaskData($taskDto, $task);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $task));
        
        return $task;
    }
    
    public function putTaskFromDto(TaskDto $taskDto, Task $task): Task
    {
        $taskDto->id = $task->getId();
        $this->validate($taskDto);
    
        $this->setTaskData($taskDto, $task);
        
        $task->setTopic($taskDto->topic);
        $task->setTaskStatus(TaskStatus::fromString($taskDto->taskStatus));
    
        $responsible = $this->userRepository->findByFullName($taskDto->responsible);
        if (!$responsible) {
            throw MissingDataException::forEntityNotFound($taskDto->responsible, User::class);
        }
        
        $task->setResponsible($responsible);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $task));
        
        return $task;
    }
}
