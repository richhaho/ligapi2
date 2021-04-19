<?php

declare(strict_types=1);


namespace App\Api\Dto;


class ToolBatchUpdatesDto implements BatchUpdateDtoInterface
{
    /**
     * @var ToolBatchUpdateDto[] $toolBatchUpdates
     */
    public array $toolBatchUpdates;
}
