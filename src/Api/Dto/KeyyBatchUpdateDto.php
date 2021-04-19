<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class KeyyBatchUpdateDto implements DtoInterface
{
    /**
     * @Assert\NotBlank()
     */
    public string $id;

    public ?string $name;
    public ?string $home;
    public ?string $owner;
    public $amount;
    public ?string $address;
    public ?string $note;
    public ?array $customFields;
    public ?string $isArchived;
    public ?string $permissionGroup;
    public ?string $profileImage;
}
