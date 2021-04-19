<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\ProjectDto;
use App\Entity\Consignment;
use App\Entity\Customer;
use App\Entity\Data\ChangeAction;
use App\Entity\project;
use App\Entity\User;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Exceptions\Domain\UnsupportedMethodException;
use App\Repository\CustomerRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Services\CurrentUserProvider;
use App\Services\ItemNumberService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectMapper implements MapperInterface
{
    
    use ValidationTrait;
    
    private CurrentUserProvider $currentUserProvider;
    private ValidatorInterface $validator;
    private CustomerRepository $customerRepository;
    private EventDispatcherInterface $eventDispatcher;
    private ItemNumberService $itemNumberService;
    private EntityManagerInterface $entityManager;
    private RequestContext $requestContext;
    private UserRepository $userRepository;
    private ProjectRepository $projectRepository;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        ValidatorInterface $validator,
        CustomerRepository $customerRepository,
        EventDispatcherInterface $eventDispatcher,
        ItemNumberService $itemNumberService,
        EntityManagerInterface $entityManager,
        RequestContext $requestContext,
        UserRepository $userRepository,
        ProjectRepository $projectRepository
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->validator = $validator;
        $this->customerRepository = $customerRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->itemNumberService = $itemNumberService;
        $this->entityManager = $entityManager;
        $this->requestContext = $requestContext;
        $this->userRepository = $userRepository;
        $this->projectRepository = $projectRepository;
    }
    
    private function setProjectData(ProjectDto $projectDto, Project $project): Project
    {
        if ($projectDto->customer && $projectDto->customer->id) {
            /** @var Customer $customer */
            $customer = $this->customerRepository->find($projectDto->customer->id);
            if (!$customer) {
                throw MissingDataException::forEntityNotFound($projectDto->customer->id, Customer::class);
            }
            $project->setCustomer($customer);
        } else {
            $project->setCustomer(null);
        }
    
        if ($projectDto->projectDate) {
            $project->setProjectDate(new DateTimeImmutable($projectDto->projectDate));
        } else {
            $project->setProjectDate(null);
        }
    
        if ($projectDto->projectEnd) {
            $project->setProjectEnd(new DateTimeImmutable($projectDto->projectEnd));
        } else {
            $project->setProjectEnd(null);
        }
        
        return $project;
    }
    
    public function supports(string $dtoName): bool
    {
        return $dtoName === ProjectDto::class;
    }
    
    /**
     * @param ProjectDto $dto
     */
    public function createEntityFromDto(DtoInterface $dto, string $userId): Project
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
    
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $this->validate($dto);
        
        $project = new Project($dto->name, $user->getCompany());
        
        $project = $this->setProjectData($dto, $project);
    
        $consignmentNumber = $this->itemNumberService->getNextItemNumber(Consignment::class, $project->getCompany());
        $projectConsignment = new Consignment($project->getCompany(), $consignmentNumber, $project);
        
        $this->entityManager->persist($projectConsignment);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $projectConsignment));
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $project));
        
        return $project;
    }
    
    /**
     * @param ProjectDto $dto
     * @param Project $entity
     */
    public function putEntityFromDto(DtoInterface $dto, object $entity): Project
    {
        $this->validate($dto);
        
        $entity->setName($dto->name);
    
        $project = $this->setProjectData($dto, $entity);
        
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $project));
        
        return $project;
    }
    
    public function patchEntityFromDto(DtoInterface $dto, object $entity)
    {
        throw UnsupportedMethodException::forUnsupportedMethod('patchEntityFromDto');
    }
    
    /**
     * @param ProjectDto $dto
     */
    public function prepareImport(DtoInterface $dto, string $userId): ?ProjectDto
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $alreadyCreatedEntity = $this->projectRepository->findByName($dto->name);
        
        if ($alreadyCreatedEntity) {
            return null;
        }
        
        return $dto;
    }
}
