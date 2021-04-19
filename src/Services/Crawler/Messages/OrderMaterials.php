<?php

declare(strict_types=1);


namespace App\Services\Crawler\Messages;


class OrderMaterials
{
    private string $materialOrderId;
    
    public function __construct(string $materialOrderId)
    {
        $this->materialOrderId = $materialOrderId;
    }
    
    public function getMaterialOrderId(): string
    {
        return $this->materialOrderId;
    }
    
}
