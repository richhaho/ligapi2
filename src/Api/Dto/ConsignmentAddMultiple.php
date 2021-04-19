<?php

declare(strict_types=1);


namespace App\Api\Dto;


class ConsignmentAddMultiple
{
    /** @var ConsignmentAddMultipleItem[] $consignmentPositions */
    public array $consignmentPositions;
    public string $consignmentName;
    public ?string $consignmentId = null;
}
