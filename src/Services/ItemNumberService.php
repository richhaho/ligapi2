<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\Company;
use App\Entity\Consignment;
use App\Entity\DirectOrder;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\MaterialOrder;
use App\Entity\Tool;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Repository\ConsignmentRepository;
use App\Repository\DirectOrderRepository;
use App\Repository\KeyyRepository;
use App\Repository\MaterialOrderRepository;
use App\Repository\MaterialRepository;
use App\Repository\ToolRepository;

class ItemNumberService
{
    private MaterialRepository $materialRepository;
    private ToolRepository $toolRepository;
    private KeyyRepository $keyyRepository;
    
    private int $currentMaterialNumber;
    private int $currentToolNumber;
    private int $currentKeyyNumber;
    private int $currentMaterialOrderNumber;
    private int $currentDirectOrderNumber;
    private int $currentConsignmentNumber;
    private DirectOrderRepository $directOrderRepository;
    private MaterialOrderRepository $materialOrderRepository;
    private ConsignmentRepository $consignmentRepository;
    
    public function __construct(
        MaterialRepository $materialRepository,
        ToolRepository $toolRepository,
        KeyyRepository $keyyRepository,
        DirectOrderRepository $directOrderRepository,
        MaterialOrderRepository $materialOrderRepository,
        ConsignmentRepository $consignmentRepository
    )
    {
        $this->materialRepository = $materialRepository;
        $this->toolRepository = $toolRepository;
        $this->keyyRepository = $keyyRepository;
        $this->directOrderRepository = $directOrderRepository;
        $this->currentMaterialNumber = 0;
        $this->currentToolNumber = 0;
        $this->currentKeyyNumber = 0;
        $this->currentMaterialOrderNumber = 0;
        $this->currentDirectOrderNumber = 0;
        $this->currentConsignmentNumber = 0;
        $this->materialOrderRepository = $materialOrderRepository;
        $this->consignmentRepository = $consignmentRepository;
    }
    
    public function getNextItemNumber(string $entityClass, Company $company): int
    {
        switch ($entityClass) {
            case Material::class:
                if ($this->currentMaterialNumber) {
                    $this->currentMaterialNumber++;
                    return $this->currentMaterialNumber;
                }
                $storedHighestNumber = $this->materialRepository->findHighestItemNumber($company);
                if (!$storedHighestNumber) {
                    $this->currentMaterialNumber = 1;
                } else {
                    $this->currentMaterialNumber = $storedHighestNumber + 1;
                }
                return $this->currentMaterialNumber;
            case Tool::class:
                if ($this->currentToolNumber) {
                    $this->currentToolNumber++;
                    return $this->currentToolNumber;
                }
                $storedHighestNumber = $this->toolRepository->findHighestItemNumber($company);
                if (!$storedHighestNumber) {
                    $this->currentToolNumber = 1;
                } else {
                    $this->currentToolNumber = $storedHighestNumber + 1;
                }
                return $this->currentToolNumber;
            case Keyy::class:
                if ($this->currentKeyyNumber) {
                    $this->currentKeyyNumber++;
                    return $this->currentKeyyNumber;
                }
                $storedHighestNumber = $this->keyyRepository->findHighestItemNumber($company);
                if (!$storedHighestNumber) {
                    $this->currentKeyyNumber = 1;
                } else {
                    $this->currentKeyyNumber = $storedHighestNumber + 1;
                }
                return $this->currentKeyyNumber;
            case MaterialOrder::class:
                if ($this->currentMaterialOrderNumber) {
                    $this->currentMaterialOrderNumber++;
                    return $this->currentMaterialOrderNumber;
                }
                $storedHighestNumber = $this->materialOrderRepository->findHighestItemNumber($company);
                if (!$storedHighestNumber) {
                    $this->currentMaterialOrderNumber = 1;
                } else {
                    $this->currentMaterialOrderNumber = $storedHighestNumber + 1;
                }
                return $this->currentMaterialOrderNumber;
            case DirectOrder::class:
                if ($this->currentDirectOrderNumber) {
                    $this->currentDirectOrderNumber++;
                    return $this->currentDirectOrderNumber;
                }
                $storedHighestNumber = $this->directOrderRepository->findHighestItemNumber($company);
                if (!$storedHighestNumber) {
                    $this->currentDirectOrderNumber = 1;
                } else {
                    $this->currentDirectOrderNumber = $storedHighestNumber + 1;
                }
                return $this->currentDirectOrderNumber;
            case Consignment::class:
                if ($this->currentConsignmentNumber) {
                    $this->currentConsignmentNumber++;
                    return $this->currentConsignmentNumber;
                }
                $storedHighestNumber = $this->consignmentRepository->findHighestItemNumber($company);
                if (!$storedHighestNumber) {
                    $this->currentConsignmentNumber = 1;
                } else {
                    $this->currentConsignmentNumber = $storedHighestNumber + 1;
                }
                return $this->currentConsignmentNumber;
        }
        
        throw InvalidArgumentException::forInvalidEntityType($entityClass, 'next item number class');
    }
}
