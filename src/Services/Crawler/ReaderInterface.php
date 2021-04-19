<?php

declare(strict_types=1);


namespace App\Services\Crawler;


use App\Api\Dto\AutoMaterialDto;
use App\Entity\Company;
use App\Entity\Supplier;
use App\Services\Crawler\Dto\AvailabilityInfosDto;

interface ReaderInterface
{
    public function login(string $username, string $password, string $customerNumber): void;

    public function logout(): void;

    public function readAvailabilityInfosForSearchTerm(string $searchTerm): AvailabilityInfosDto;

    public function readAllDetails(string $searchTerm, Company $company): AutoMaterialDto;

    public function orderMaterial(string $orderNumber, float $amount, ?float $expectedPrice): string;
    
    public function supports(Supplier $supplier): bool;

    public function getName(): string;
    
    public function quit(): void;
}
