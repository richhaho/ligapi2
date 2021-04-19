<?php

declare(strict_types=1);


namespace App\Api\Dto;


class CreateConsignmentDto
{
    public ?string $projectName = null;
    
    public ?string $userFullName = null;
    
    public ?string $name = null;
    
    public ?string $note = null;
    
    public ?string $location = null;
    
    public ?string $deliveryDate = null;
    
    public ?string $deliveryAddress = null;
}
