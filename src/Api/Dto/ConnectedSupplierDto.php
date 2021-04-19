<?php


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class ConnectedSupplierDto
{
    /**
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    public ?string $id = null;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    public string $name;
}
