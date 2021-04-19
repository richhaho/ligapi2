<?php

declare(strict_types=1);


namespace App\Api\Dto;


class EventData
{
    public ?string $id;
    public string $itemType;
    public string $action;
}
