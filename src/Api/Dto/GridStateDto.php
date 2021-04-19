<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class GridStateDto
{
    public ?string $id = null;
    public ?string $name = null;
    /**
     * @Assert\NotBlank()
     */
    public string $gridType;
    /**
     * @Assert\NotBlank()
     */
    public string $ownerType;
    public ?string $columnState = "";
    public ?string $sortState = "";
    public ?string $filterState = "";
    public ?int $paginationState = 100;
    public ?bool $isDefault = false;
    public ?PutUser $putUser = null;
}
