<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Data\ItemGroupType;
use App\Entity\Data\LocationCategory;
use App\Entity\ItemGroup;
use App\Entity\Keyy;
use App\Entity\Location;
use App\Entity\Material;
use App\Entity\MaterialLocation;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use App\Entity\Tool;
use App\Security\UserService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Money\Money;

class LouisFixtures extends Fixture implements DependentFixtureInterface
{
    
    private UserService $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function load(ObjectManager $manager)
    {
        return;
        
        $company3 = new Company('Louis', true);
        $company3->setCountry('South Africa');
        $manager->persist($company3);

        $user = $this->userService->createUser(
            'Louis',
            'Botha',
            'lb@test.com',
            'test1234',
            $company3
        );
        $user->setIsAdmin(true);
        $manager->persist($user);

        $materialCategory = new ItemGroup('Paints', ItemGroupType::material(), $company3);
        $manager->persist($materialCategory);

        $supplier = new Supplier('supp', $company3);
        $manager->persist($supplier);

        $material = new Material("lotter3", 'new', $company3);
        $material->setSellingPrice(Money::EUR(100));
        $material->setBarcode('barcode123');
        $material->setManufacturerName('manufacture');
        $material->setManufacturerNumber('333');
        $material->setPermanentInventory(true);
        $material->setUnit('St');
        $material->setUnitAlt('kg');
        $material->setUsableTill(new DateTimeImmutable());
        $material->setUnitConversion(5);
        $material->setItemGroup($materialCategory);
        $manager->persist($material);

        $location = Location::forCompany('loc', $company3);
        $manager->persist($location);

        $materialLocation = new MaterialLocation($company3, LocationCategory::main(), $material, $location);
        $materialLocation->setCurrentStock(10);
        $materialLocation->setCurrentStockAlt(100);
        $materialLocation->setMinStock(5);
        $materialLocation->setMaxStock(50);

        $manager->persist($materialLocation);

        $orderSource = new OrderSource('331', 1, $material, $supplier, $company3);
        $orderSource->setPrice(4);
        $manager->persist($orderSource);

        $toolCategory = new ItemGroup('Handtool', ItemGroupType::tool(), $company3);
        $manager->persist($toolCategory);

        $tool = new Tool('1', 'Hammer', $location, $location, $company3);
        $tool->setItemGroup($toolCategory);
        $tool->setManufacturerNumber('3123');
        $tool->setManufacturerName('Bosch');
        $tool->setBarcode('bar3213');
        $tool->setPurchasingPrice(100);
        $tool->setPurchasingDate(new DateTimeImmutable());
        $manager->persist($tool);

        $keyy = new Keyy('1', 'House Key', $location, $location, $company3);
        $keyy->setAddress('Main Street 3');
        $keyy->setAmount(3);
        $manager->persist($keyy);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }
}
