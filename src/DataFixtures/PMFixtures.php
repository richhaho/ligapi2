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
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PMFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        return;
        
        $company = $manager->getRepository(Company::class)
            ->findOneBy(['name' => 'Lager im Griff']);

        $connectedSupplier = new ConnectedSupplier('PFEIFFER & MAY', 'https://online.pfeiffer-may.de:8443');
        $manager->persist($connectedSupplier);

        $supplier = new Supplier('PFEIFFER & MAY', $company);
        $supplier->setWebShopLogin('kontakt@steffengrell.de');
        $supplier->setwebShopPassword('cEsuHodPHfEbtiTdr7sh+KWE/yzf+gTB6qfNTZGWIRxaFBWYyLUTVSDSXHNKAFiy7MIbZmLZ0Q==');
        $supplier->setConnectedSupplier($connectedSupplier);
        $manager->persist($supplier);

        $material = new Material("pm1", 'new pm', $company);
        $material->setAutoSearchTerm('MEW20K');
        $material->setAutoSupplier($supplier);
        $material->setAutoStatus(AutoStatus::new());
        $manager->persist($material);

        $orderSource = new OrderSource('MEW20K', 1, $material, $supplier, $company);
        $orderSource->setPrice(13);
        $orderSource->setLastPriceUpdate(new DateTimeImmutable());
        $orderSource->setAutoStatus(AutoStatus::new());
        $orderSource->setLastAutoSet(new DateTimeImmutable());
        $manager->persist($orderSource);

        $order = new MaterialOrder(MaterialOrderType::webshop(), $orderSource->getSupplier(), $company, 3);
        $manager->persist($order);

        $orderPosition = new MaterialOrderPosition($company, 2, $orderSource, $order);
        $manager->persist($orderPosition);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }
}
