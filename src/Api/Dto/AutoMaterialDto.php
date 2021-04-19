<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\HttpFoundation\File\File;

class AutoMaterialDto
{
    /**
     * @var OrderSourceDto[] $orderSources
     */
    public array $orderSources;
    public ?float $sellingPrice = null;
    public string $name;
    public ?string $note = null;
    public ?string $unit = null;
    public ?string $manufacturerName = null;
    public ?string $manufacturerNumber = null;
    public ?File $imgFile = null;
}
