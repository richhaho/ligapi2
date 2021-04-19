<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\CreateConsignmentDto;
use App\Api\Dto\PutConsignmentDto;
use App\Entity\Consignment;
use App\Entity\Data\ChangeAction;
use App\Entity\Project;
use App\Entity\User;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Services\CurrentUserProvider;
use App\Services\ItemNumberService;
use App\Services\LocationService;
use DateTimeImmutable;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConsignmentMapper
{
    use ValidationTrait;
    
    private CurrentUserProvider $currentUserProvider;
    private ProjectRepository $projectRepository;
    private UserRepository $userRepository;
    private EventDispatcherInterface $eventDispatcher;
    private ValidatorInterface $validator;
    private LocationService $locationService;
    private ItemNumberService $itemNumberService;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        ProjectRepository $projectRepository,
        UserRepository $userRepository,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        LocationService $locationService,
        ItemNumberService $itemNumberService
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->locationService = $locationService;
        $this->itemNumberService = $itemNumberService;
    }
    
    public function createConsignmentFromDto(CreateConsignmentDto $consignmentDto): Consignment
    {
        $this->validate($consignmentDto);
        
        $company = $this->currentUserProvider->getAuthenticatedUser()->getCompany();
        
        $project = null;
        $user = null;
        
        if ($consignmentDto->projectName) {
            $project = $this->projectRepository->findByName($consignmentDto->projectName);
            if (!$project) {
                throw MissingDataException::forEntityNotFound($consignmentDto->projectName, Project::class, 'name');
            }
        }
        
        if ($consignmentDto->userFullName) {
            $user = $this->userRepository->findByFullName($consignmentDto->userFullName);
            if (!$user) {
                throw MissingDataException::forEntityNotFound($consignmentDto->userFullName, User::class, 'full name');
            }
        }
    
        $consignmentNumber = $this->itemNumberService->getNextItemNumber(Consignment::class, $company);
        
        $consignment = new Consignment($company, $consignmentNumber, $project, $user, $consignmentDto->name);
        
        $consignment->setNote($consignmentDto->note);
    
        if ($consignmentDto->deliveryDate) {
            $consignment->setDeliveryDate(new DateTimeImmutable($consignmentDto->deliveryDate));
        } else {
            $consignment->setDeliveryDate(null);
        }
    
        if ($consignmentDto->location) {
            $location = $this->locationService->mapStringToLocation($consignmentDto->location, $company);
            $consignment->setLocation($location);
        }
        
        if ($consignmentDto->deliveryAddress) {
            $consignment->setDeliveryAddress($consignmentDto->deliveryAddress);
        }
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $consignment));
        
        return $consignment;
    }
    
    public function putConsignmentFromDto(PutConsignmentDto $consignmentDto, Consignment $consignment): Consignment
    {
        $this->validate($consignmentDto);
        
        $consignment->setNote($consignmentDto->note);
    
        if ($consignmentDto->deliveryDate) {
            $consignment->setDeliveryDate(new DateTimeImmutable($consignmentDto->deliveryDate));
        } else {
            $consignment->setDeliveryDate(null);
        }
        
        $consignment->setName($consignmentDto->name);
    
        if ($consignmentDto->location) {
            $location = $this->locationService->mapStringToLocation($consignmentDto->location, $consignment->getCompany());
            $consignment->setLocation($location);
        } else {
            $consignment->setLocation(null);
        }
    
        $consignment->setDeliveryAddress($consignmentDto->deliveryAddress ?? '');
        
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $consignment));
        
        return $consignment;
    }
}
