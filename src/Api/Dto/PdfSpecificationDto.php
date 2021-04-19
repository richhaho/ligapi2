<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class PdfSpecificationDto
{
    /**
     * @Assert\NotBlank()
     */
    public string $id;
}
