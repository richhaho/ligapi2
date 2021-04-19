<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class ConsignmentAddMultipleItem
{
    public ?string $id = null;
    /**
     * @Assert\NotNull()
     */
    public string $name;
    /**
     * @Assert\NotNull()
     */
    public float $amount;
    /**
     * @Assert\NotNull()
     */
    public string $entityType;
}
