<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\ConnectedSupplier;
use App\Entity\Data\AutoStatus;
use App\Entity\Data\MaterialOrderType;
use App\Entity\Material;
use App\Entity\MaterialOrder;
use App\Entity\MaterialOrderPosition;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use App\Security\UserService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReisserFixtures extends Fixture implements DependentFixtureInterface
{
    private UserService $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function load(ObjectManager $manager)
    {
        return;
        
        $company = $manager->getRepository(Company::class)
            ->findOneBy(['name' => 'Lager im Griff']);

        $connectedSupplier = new ConnectedSupplier('Reisser', 'https://reisser.sct.de/');
        $manager->persist($connectedSupplier);

        $supplier = new Supplier('Reisser', $company);
        $supplier->setWebShopLogin('SG');
        $supplier->setwebShopPassword('qWZ97JLkIQ9iZg9cHOiOC7eXAA+TSH0ycGw6vQShQyQgr1kp8hEmGGKPKuT7TLheYOAyK8cVoA==');
        $supplier->setConnectedSupplier($connectedSupplier);
        $supplier->setCustomerNumber('30037530');
        $manager->persist($supplier);

        $material = new Material("reisser1", 'new reisser', $company);
        $material->setAutoSearchTerm('VZ602/271');
        $material->setAutoSupplier($supplier);
        $material->setAutoStatus(AutoStatus::new());
        $manager->persist($material);

        $priceUpdateMaterial = new Material(100, 'Rotguss-T-Stück Nr.3130, 90 Grad DN15 (1/2"), 3 Innengewinde', $company);
        $manager->persist($priceUpdateMaterial);

        $orderSource = new OrderSource('VZ602/271', 1, $priceUpdateMaterial, $supplier, $company);
        $orderSource->setPrice(13);
        $orderSource->setLastPriceUpdate(new DateTimeImmutable());
        $orderSource->setAutoStatus(AutoStatus::new());
        $orderSource->setLastAutoSet(new DateTimeImmutable());
        $manager->persist($orderSource);

        $order = new MaterialOrder(MaterialOrderType::webshop(), $orderSource->getSupplier(), $company, 4);
        $manager->persist($order);

        $orderPosition1 = new MaterialOrderPosition($company, 2, $orderSource, $order);
        $manager->persist($orderPosition1);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            AppFixtures::class,
        ];
    }
}
