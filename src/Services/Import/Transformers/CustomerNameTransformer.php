<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\CustomerDto;
use App\Api\Dto\DtoInterface;
use App\Entity\Customer;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\CustomerRepository;

class CustomerNameTransformer implements TransformerInterface
{
    
    private CustomerRepository $customerRepository;
    
    public function __construct(
        CustomerRepository $customerRepository
    )
    {
        $this->customerRepository = $customerRepository;
    }
    
    public function supports(string $transformer): bool
    {
        return $transformer === CustomerNameTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?CustomerDto
    {
        $customerName = $data[$title];
        
        if (!$customerName) {
            return null;
        }
        
        $customerDto = new CustomerDto();
        
        $customer = $this->customerRepository->getByName($customerName);
        
        if (!$customer) {
            throw MissingDataException::forEntityNotFound($customerName, Customer::class, 'name');
        }
        
        $customerDto->id = $customer->getId();
        $customerDto->name = $customer->getName();
        
        return $customerDto;
    }
}
