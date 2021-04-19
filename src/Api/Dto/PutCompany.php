<?php

declare(strict_types=1);


namespace App\Api\Dto;


use App\Validator\GlobalUnique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @GlobalUnique(entity="App\Entity\Company", property="name")
 */
class PutCompany
{
    public ?string $id = null;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     * @Assert\NotBlank()
     */
    public string $name;

    public ?string $city = null;
    public ?string $country = null;
    public ?string $fax = null;
    public ?string $phone = null;
    public ?string $street = null;
    public ?string $website = null;
    public ?string $zip = null;
    /**
     * @Assert\Email()
     */
    public ?string $invoiceEmail = null;
    /**
     * @Assert\Email()
     */
    public ?string $orderEmail = null;
    public ?string $addressLine1 = null;
    public ?string $addressLine2 = null;

    public ?int $userAmount = null;
    public ?string $paymentType = null;
    public ?string $paymentCycle = null;
    
    public ?int $currentMaterialLabel = 1;
}
