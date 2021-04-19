<?php


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class MaterialLocationDto implements DtoInterface
{
    public ?string $id = null;
    
    public ?string $materialLocationId = null;
    
    public ?string $name = null;
    
    public ?ProjectDto $project = null;
    
    public ?string $locationCategory = "main";
    
    /**
     * @Assert\Range(min=0)
     */
    public ?float $minStock = null;
    
    /**
     * @Assert\Range(min=0)
     */
    public ?float $maxStock = null;
    
    /**
     * @Assert\Range(min=0)
     */
    public ?float $currentStock = null;
    
    /**
     * @Assert\Range(min=0)
     */
    public ?float $currentStockAlt = null;
    
    public PutMaterialDto $material;
    
    public ?string $stockChangeNote = null;
}
