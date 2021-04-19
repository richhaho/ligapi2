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

class SchmidtRudersdorfFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        return;
        
        $company = $manager->getRepository(Company::class)
            ->findOneBy(['name' => 'Lager im Griff']);

        $connectedSupplier = new ConnectedSupplier('Schmidt Rudersdorf', 'https://schmidt-rudersdorf.shop/');
        $manager->persist($connectedSupplier);

        $supplier = new Supplier('Schmidt Rudersdorf', $company);
        $supplier->setWebShopLogin('kontakt@lig-pro.de');
        $supplier->setwebShopPassword('JSPjyyD+4XiDXTR2HgIgHncdrnFb+VCBo1RQPpjkgwcqAGQaTlmVb0ebFSbLMzTU9W/iMSyujq39VwQAeg==');
        $supplier->setConnectedSupplier($connectedSupplier);
        $manager->persist($supplier);

        $material = new Material("schmidtRu1", 'new Schmidt Rudersdorf', $company);
        $material->setAutoSearchTerm('76110318');
        $material->setAutoSupplier($supplier);
        $material->setAutoStatus(AutoStatus::new());
        $manager->persist($material);

        $orderSource = new OrderSource('76110318', 1, $material, $supplier, $company);
        $orderSource->setPrice(13);
        $orderSource->setLastPriceUpdate(new DateTimeImmutable());
        $orderSource->setAutoStatus(AutoStatus::new());
        $orderSource->setLastAutoSet(new DateTimeImmutable());
        $manager->persist($orderSource);

        $order = new MaterialOrder(MaterialOrderType::webshop(), $orderSource->getSupplier(), $company, 5);
        $manager->persist($order);

        $orderPosition1 = new MaterialOrderPosition($company, 2, $orderSource, $order);
        $manager->persist($orderPosition1);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }
}
