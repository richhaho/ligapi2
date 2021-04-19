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

class LotterFixtures extends Fixture implements DependentFixtureInterface
{
    private UserService $userService;
    
    public function __construct(
        UserService $userService
    )
    {
        $this->userService = $userService;
    }
    
    public function load(ObjectManager $manager)
    {
        return;
        
        $company = $manager->getRepository(Company::class)
            ->findOneBy(['name' => 'Lager im Griff']);

        $connectedSupplier = new ConnectedSupplier('Lotter', 'https://www.lotter24.de/start');
        $manager->persist($connectedSupplier);

        $supplier = new Supplier('Lotter', $company);
        $supplier->setWebShopLogin('steffen grell');
        $supplier->setWebShopPassword('sRTmUriTTf7FzyYtGekh1ZoGytvP3d8+jJwcD5yYOVakRo4B+IcttqvSFgTyAc912G4Z7kb9/sUZdME=');
        $supplier->setConnectedSupplier($connectedSupplier);
        $manager->persist($supplier);

        $material = new Material("lotter1", 'new lotter', $company);
        $material->setAutoSearchTerm('G622271');
        $material->setAutoSupplier($supplier);
        $material->setAutoStatus(AutoStatus::new());
        $manager->persist($material);

        $priceUpdateMaterial = new Material("lotter2", 'Rotguss Stopfen, Nr. 290 1/2"', $company);
        $manager->persist($priceUpdateMaterial);
        
        $orderSource = new OrderSource('329015', 1, $priceUpdateMaterial, $supplier, $company);
        $orderSource->setPrice(13);
        $orderSource->setLastPriceUpdate(new DateTimeImmutable());
        $orderSource->setAutoStatus(AutoStatus::new());
        $orderSource->setLastAutoSet(new DateTimeImmutable());
        $manager->persist($orderSource);

        $order = new MaterialOrder(MaterialOrderType::webshop(), $orderSource->getSupplier(), $company, 1);
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
