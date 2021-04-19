<?php

declare(strict_types=1);


namespace App\Api\Dto;


use App\Validator\GlobalUnique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @GlobalUnique(entity="App\Entity\Company", property="name", target="name")
 */
class CompanyDto
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     */
    public string $name;
    
    /**
     * @Assert\NotBlank()
     */
    public bool $termsAccepted;
}
