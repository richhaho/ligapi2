<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class CreateConsignmentItemDto
{
    /**
     * @Assert\NotNull()
     */
    public string $consignmentId;
    public ?string $materialId = null;
    public ?string $toolId = null;
    public ?string $keyyId = null;
    public ?string $manualName = null;
    public ?float $amount = null;
}
