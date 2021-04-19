<?php

declare(strict_types=1);


namespace App\Services\Crawler\Messages;


class UpdatePrices
{
    /**
     * @var string[] $orderSourceIds
     */
    private array $orderSourceIds;
    
    public function __construct(array $orderSourceIds)
    {
        $this->orderSourceIds = $orderSourceIds;
    }
    
    public function getOrderSourceIds(): array
    {
        return $this->orderSourceIds;
    }
    
}
