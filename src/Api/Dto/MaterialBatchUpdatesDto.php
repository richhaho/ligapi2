<?php

declare(strict_types=1);


namespace App\Api\Dto;


class MaterialBatchUpdatesDto implements BatchUpdateDtoInterface
{
    /**
     * @var MaterialBatchUpdateDto[] $materialBatchUpdates
     */
    public array $materialBatchUpdates;
}
