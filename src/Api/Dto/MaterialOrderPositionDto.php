<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class MaterialOrderPositionDto
{
    /**
     * @Assert\NotBlank()
     */
    public int $amount;
    /**
     * @Assert\NotBlank()
     */
    public OrderSourceDto $orderSource;
    
    public ?DirectOrderPositionResultDto $directOrderPositionResult = null;
}
