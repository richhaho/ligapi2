<?php


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class OrderSourceDto implements DtoInterface
{
    public ?string $id = null;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    public string $orderNumber;
    
    /**
     * @Assert\NotBlank()
     */
    public SupplierDto $supplier;
    
    public ?string $note = null;
    
    /**
     * @Assert\Range(min=1)
     */
    public ?float $amountPerPurchaseUnit = null;
    
    /**
     * @Assert\Range(min=0)
     */
    public ?float $price = null;
    
    public int $priority = 1;
    
    public PutMaterialDto $material;
    
}
