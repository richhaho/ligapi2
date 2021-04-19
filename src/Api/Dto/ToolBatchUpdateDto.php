<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class ToolBatchUpdateDto implements DtoInterface
{
    /**
     * @Assert\NotBlank()
     */
    public string $id;

    public ?string $name;
    public ?string $itemGroup;
    public ?string $manufacturerName;
    public ?string $manufacturerNumber;
    public ?string $home;
    public ?string $owner;
    public ?string $usableTill;
    public ?bool $isBroken;
    public ?string $purchasingPrice;
    public ?string $barcode;
    public ?string $blecode;
    public ?string $purchasingDate;
    public ?string $note;
    public ?array $customFields;
    public ?string $profileImage;
    public ?string $permissionGroup;
}
