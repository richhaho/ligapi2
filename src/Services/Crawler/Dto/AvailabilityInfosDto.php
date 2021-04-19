<?php

declare(strict_types=1);


namespace App\Services\Crawler\Dto;


use Money\Money;

class AvailabilityInfosDto
{
    private Money $price;
    private string $availability;
    
    public function __construct(Money $price, string $availability)
    {
        $this->price = $price;
        $this->availability = $availability;
    }
    
    public function getPrice(): Money
    {
        return $this->price;
    }
    
    public function getAvailability(): string
    {
        return $this->availability;
    }
}
