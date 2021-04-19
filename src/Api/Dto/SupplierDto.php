<?php


namespace App\Api\Dto;


use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entity="App\Entity\Supplier", properties={"name"})
 */
class SupplierDto implements DtoInterface
{
    public ?string $id = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $name = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $customerNumber = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $webShopLogin = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $webShopPassword = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $responsiblePerson = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $street = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $zipCode = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $city = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $country = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $email = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $phone = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $fax = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $emailSalutation = null;
    
    public ?ConnectedSupplierDto $connectedSupplier = null;
}
