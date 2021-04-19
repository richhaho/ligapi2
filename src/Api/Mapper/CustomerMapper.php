<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\CustomerDto;
use App\Api\Dto\DtoInterface;
use App\Entity\Customer;
use App\Entity\Data\ChangeAction;
use App\Entity\User;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Exceptions\Domain\UnsupportedMethodException;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use App\Services\CurrentUserProvider;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CustomerMapper implements MapperInterface
{
    
    use ValidationTrait;
    
    private CurrentUserProvider $currentUserProvider;
    private ValidatorInterface $validator;
    private EventDispatcherInterface $eventDispatcher;
    private CustomerRepository $customerRepository;
    private UserRepository $userRepository;
    private RequestContext $requestContext;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        CustomerRepository $customerRepository,
        UserRepository $userRepository,
        RequestContext $requestContext
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
        $this->customerRepository = $customerRepository;
        $this->userRepository = $userRepository;
        $this->requestContext = $requestContext;
    }
    
    private function setCustomerData(Customer $customer, CustomerDto $customerDto): Customer
    {
        $customer->setStreet($customerDto->street);
        $customer->setZip($customerDto->zip);
        $customer->setCity($customerDto->city);
        $customer->setCountry($customerDto->country);
        $customer->setEmail($customerDto->email);
        $customer->setFirstName($customerDto->firstName);
        $customer->setLastName($customerDto->lastName);
        $customer->setShippingStreet($customerDto->shippingStreet);
        $customer->setShippingZip($customerDto->shippingZip);
        $customer->setShippingCity($customerDto->shippingCity);
        $customer->setShippingCountry($customerDto->shippingCountry);
        $customer->setPhone($customerDto->phone);
        
        return $customer;
    }
    
    public function supports(string $dtoName): bool
    {
        return $dtoName === CustomerDto::class;
    }
    
    /**
     * @param CustomerDto $dto
     */
    public function createEntityFromDto(DtoInterface $dto, string $userId): Customer
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
    
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $this->validate($dto);
        
        $customer = new Customer($dto->name, $user->getCompany());
    
        $customer = $this->setCustomerData($customer, $dto);
        
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $customer));
        
        return $customer;
    }
    
    /**
     * @param CustomerDto $dto
     * @param Customer $entity
     */
    public function putEntityFromDto(DtoInterface $dto, object $entity): Customer
    {
        $this->validate($dto);
        
        $entity->setName($dto->name);
        
        $entity = $this->setCustomerData($entity, $dto);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
        
        return $entity;
    }
    
    public function patchEntityFromDto(DtoInterface $dto, object $entity)
    {
        throw UnsupportedMethodException::forUnsupportedMethod('patchEntityFromDto');
    }
    
    /**
     * @param CustomerDto $dto
     */
    public function prepareImport(DtoInterface $dto, string $userId): ?CustomerDto
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $alreadyCreatedEntity = $this->customerRepository->getByName($dto->name);
        
        if ($alreadyCreatedEntity) {
            return null;
        }
        
        return $dto;
    }
}
