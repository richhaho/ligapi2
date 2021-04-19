<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class PutConsignmentDto
{
    /**
     * @Assert\NotNull()
     */
    public string $id;
    
    /**
     * @Assert\Length(min=1)
     */
    public ?string $name = null;
    
    public ?string $note = null;
    
    public ?string $location = null;
    
    public ?string $deliveryDate = null;
    
    public ?string $deliveryAddress = null;
}
