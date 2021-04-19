<?php

declare(strict_types=1);


namespace App\Api\Dto;


use App\Validator\GlobalUnique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @GlobalUnique(entity="App\Entity\User", property="email")
 */
class RegisterDto
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @Assert\Length(min=8)
     */
    public string $password;
    
    public CompanyDto $company;
    
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     */
    public string $firstName;
    
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     */
    public string $lastName;
    
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     * @Assert\Email()
     */
    public string $email;
}
