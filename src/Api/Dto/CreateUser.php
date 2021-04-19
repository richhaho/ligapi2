<?php

declare(strict_types=1);


namespace App\Api\Dto;


use App\Validator\GlobalUnique;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @GlobalUnique(entity="App\Entity\User", property="email")
 * @Unique(entity="App\Entity\User", properties={"firstName", "lastName"})
 */
class CreateUser implements DtoInterface
{
    /**
     * @Assert\Length(max=255)
     * @Assert\Email()
     * @Assert\NotBlank()
     */
    public string $email;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=8)
     * @Assert\NotBlank()
     */
    public string $password;
    
    public ?iterable $permissions = [];
    
    public ?bool $isAdmin = false;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    public string $firstName;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    public string $lastName;
}
