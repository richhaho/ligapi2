<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class PdfDocumentTypeDto
{
    public ?string $id = null;
    
    public ?array $itemFields = null;
    
    public ?array $commonFields = null;
    
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     */
    public string $name;
    
    /**
     * @Assert\NotBlank()
     */
    public PdfSpecificationDto $pdfSpecification;
    
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     */
    public string $entityType;
}
