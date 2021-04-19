<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\ApiContext;
use App\Api\Dto\CustomerDto;
use App\Api\Mapper\CustomerMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Customer;
use App\Entity\Project;
use App\Event\ChangeEvent;
use App\Repository\CustomerRepository;
use App\Services\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/customers/", name="api_customer_")
 */
class CustomerController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_customer_get")
     */
    public function index(CustomerRepository $repository, EntityManagerInterface $entityManager): iterable
    {
        $entityManager->getFilters()->enable('deleted');
        
        return $repository->findBy([], ['name' => 'ASC']);
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_customer_get")
     */
    public function get(Customer $customer): Customer
    {
        return $customer;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_customer_get")
     */
    public function create(
        CustomerDto $createCustomer,
        CustomerMapper $mapper,
        EntityManagerInterface $em,
        Security $security,
        CurrentUserProvider $currentUserProvider
    ): Customer
    {
        if(!$security->isGranted(Permission::ADMIN)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $userId = $currentUserProvider->getAuthenticatedUser()->getId();
        $customer = $mapper->createEntityFromDto($createCustomer, $userId);
        $em->persist($customer);
        $em->flush();
        
        return $customer;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     */
    public function put(
        CustomerDto $customerDto,
        CustomerMapper $mapper,
        Customer $customer,
        EntityManagerInterface $em,
        Security $security
    ) : Customer
    {
        if(!$security->isGranted(Permission::ADMIN)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $customer = $mapper->putEntityFromDto($customerDto, $customer);
        $em->flush();
        return $customer;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        Customer $customer,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::ADMIN)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        /** @var Project $project */
        foreach ($customer->getProjects() as $project) {
            $project->setDeleted(true);
            $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $project));
        }
        
        $customer->setDeleted(true);
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $customer));
        $entityManager->flush();
        
        return new Response(null, 204);
    }
}
