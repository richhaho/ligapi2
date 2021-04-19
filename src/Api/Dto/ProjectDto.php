<?php

declare(strict_types=1);


namespace App\Api\Dto;


use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entity="App\Entity\Project", properties={"name"})
 */
class ProjectDto implements DtoInterface
{
    public ?string $id = null;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     * @Assert\NotBlank()
     */
    public string $name;
    
    public ?CustomerDto $customer = null;
    
    public ?string $projectDate = null;
    public ?string $projectEnd = null;
}
