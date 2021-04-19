<?php

declare(strict_types=1);


namespace App\Api\Dto;


use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entity="App\Entity\CustomField", properties={"name"})
 */
class CustomFieldDto
{
    public ?string $id;
    /**
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    public string $name;
    /**
     * @Assert\Length(max=10)
     * @Assert\NotBlank()
     */
    public string $type;
    public ?array $options = null;
    public string $entityType;
}
