<?php

declare(strict_types=1);


namespace App\Api\Dto;


use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entity="App\Entity\Customer", properties={"name"})
 */
class CustomerDto implements DtoInterface
{
    public ?string $id = null;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     * @Assert\NotBlank()
     */
    public string $name;

    public ?string $street = null;
    public ?string $zip = null;
    public ?string $city = null;
    public ?string $country = null;
    public ?string $email = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $shippingStreet = null;
    public ?string $shippingZip = null;
    public ?string $shippingCity = null;
    public ?string $shippingCountry = null;
    public ?string $phone = null;
}
