<?php


namespace App\Api\Mapper;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\SupplierDto;
use App\Entity\ConnectedSupplier;
use App\Entity\Data\ChangeAction;
use App\Entity\Supplier;
use App\Entity\User;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Exceptions\Domain\UnsupportedMethodException;
use App\Repository\ConnectedSupplierRepository;
use App\Repository\SupplierRepository;
use App\Repository\UserRepository;
use App\Security\Secrets\ValueEncryptInterface;
use App\Services\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SupplierMapper implements MapperInterface
{
    use ValidationTrait;
    
    private EntityManagerInterface $entityManager;
    private SupplierRepository $supplierRepository;
    private CurrentUserProvider $currentUserProvider;
    private EventDispatcherInterface $eventDispatcher;
    private ValueEncryptInterface $encrypter;
    private ValidatorInterface $validator;
    private ConnectedSupplierRepository $connectedSupplierRepository;
    private UserRepository $userRepository;
    private RequestContext $requestContext;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        CurrentUserProvider $currentUserProvider,
        SupplierRepository $supplierRepository,
        EventDispatcherInterface $eventDispatcher,
        ValueEncryptInterface $encrypter,
        ValidatorInterface $validator,
        ConnectedSupplierRepository $connectedSupplierRepository,
        UserRepository $userRepository,
        RequestContext $requestContext
    )
    {
        $this->entityManager = $entityManager;
        $this->supplierRepository = $supplierRepository;
        $this->currentUserProvider = $currentUserProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->encrypter = $encrypter;
        $this->validator = $validator;
        $this->connectedSupplierRepository = $connectedSupplierRepository;
        $this->userRepository = $userRepository;
        $this->requestContext = $requestContext;
    }
    
    public function supports(string $dtoName): bool
    {
        return $dtoName === SupplierDto::class;
    }
    
    private function setSupplierData(Supplier $supplier, SupplierDto $supplierDto): Supplier
    {
        $supplier->setCustomerNumber($supplierDto->customerNumber);
        $supplier->setWebShopLogin($supplierDto->webShopLogin);
        if ($supplierDto->webShopPassword) {
            $encryptedPassword = $this->encrypter->encrypt($supplierDto->webShopPassword);
            $supplier->setwebShopPassword($encryptedPassword);
        }
        $supplier->setResponsiblePerson($supplierDto->responsiblePerson);
        $supplier->setStreet($supplierDto->street);
        $supplier->setZipCode($supplierDto->zipCode);
        $supplier->setCity($supplierDto->city);
        $supplier->setCountry($supplierDto->country);
        $supplier->setEmail($supplierDto->email);
        $supplier->setPhone($supplierDto->phone);
        $supplier->setFax($supplierDto->fax);
        $supplier->setEmailSalutation($supplierDto->emailSalutation);
        
        if ($supplierDto->connectedSupplier && $supplierDto->connectedSupplier->name) {
            $connectedSupplier = $this->connectedSupplierRepository->getByName($supplierDto->connectedSupplier->name);
            if (!$connectedSupplier) {
                throw MissingDataException::forEntityNotFound($supplierDto->connectedSupplier->name, ConnectedSupplier::class);
            }
            $supplier->setConnectedSupplier($connectedSupplier);
        } else {
            $supplier->setConnectedSupplier(null);
        }
        
        return $supplier;
    }
    
    /**
     * @param SupplierDto $dto
     */
    public function createEntityFromDto(DtoInterface $dto, string $userId): Supplier
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $this->validate($dto);
        
        $supplier = new Supplier($dto->name, $user->getCompany());
        $this->setSupplierData($supplier, $dto);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $supplier));
    
        return $supplier;
    }
    
    /**
     * @param SupplierDto $dto
     * @param Supplier $object
     */
    public function putEntityFromDto(DtoInterface $dto, object $object): Supplier
    {
        $dto->id = $object->getId();
        $this->validate($dto);
        
        $object->setName($dto->name);
        $this->setSupplierData($object, $dto);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $object));
        
        return $object;
    }
    
    
    public function patchEntityFromDto(DtoInterface $dto, object $entity)
    {
        throw UnsupportedMethodException::forUnsupportedMethod('patchEntityFromDto');
    }
    
    /**
     * @param SupplierDto $dto
     */
    public function prepareImport(DtoInterface $dto, string $userId): ?SupplierDto
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
    
        $alreadyCreatedSupplier = $this->supplierRepository->findByName($dto->name);
    
        if ($alreadyCreatedSupplier) {
            return null;
        }
        
        return $dto;
    }
}
