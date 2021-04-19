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
class PutUser implements DtoInterface
{
    public ?string $id = null;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\Email()
     * @Assert\NotBlank()
     */
    public string $email;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=8)
     */
    public ?string $password = null;
    
    public ?iterable $permissions = null;
    
    public ?bool $isAdmin = null;
    
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
