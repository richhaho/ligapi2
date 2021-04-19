<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class StocktakingDto
{
    /**
     * @Assert\NotNull()
     */
    public float $currentStock;
    public ?float $currentStockAlt = null;
    public ?string $stockChangeNote = null;
    public ?string $projectId = null;
}
