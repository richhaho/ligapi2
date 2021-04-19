<?php

declare(strict_types=1);


namespace App\Api\Dto;


class StockChangeDto implements DtoInterface
{
    public string $originalId;
    public ?string $note = null;
    public ?float $amount = null;
    public ?float $newCurrentStock = null;
    public ?float $amountAlt = null;
    public ?float $newCurrentStockAlt = null;
    public string $materialLocationId;
    public string $createdAt;
    public ?MaterialLocationDto $materialLocationDto = null;
    public ?IdDto $user = null;
}
