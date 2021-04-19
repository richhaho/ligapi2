<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\ConnectedSupplier;
use App\Entity\DirectOrder;
use App\Entity\DirectOrderPosition;
use App\Entity\DirectOrderPositionResult;
use App\Entity\Material;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use App\Security\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DirectOrderFixtures extends Fixture implements DependentFixtureInterface
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
        $company = $manager->getRepository(Company::class)
            ->findOneBy(['name' => 'Lager im Griff']);

        // CREATE CONNECTED SUPPLIERS
        
        $connectedSupplierLotter = new ConnectedSupplier('Lotter', 'https://www.lotter24.de/start');
        $manager->persist($connectedSupplierLotter);

        $connectedSupplierReisser = new ConnectedSupplier('Reisser', 'https://reisser.sct.de/');
        $manager->persist($connectedSupplierReisser);
    
        $connectedSupplierPM = new ConnectedSupplier('PFEIFFER & MAY', 'https://online.pfeiffer-may.de:8443');
        $manager->persist($connectedSupplierPM);
    
        $connectedSupplierGC = new ConnectedSupplier('GC', 'https://gconlineplus.de/');
        $manager->persist($connectedSupplierGC);
    
        // CREATE SUPPLIERS
        
        $supplierLotter = new Supplier('Lotter DO', $company);
        $supplierLotter->setWebShopLogin('steffen grell');
        $supplierLotter->setwebShopPassword('sRTmUriTTf7FzyYtGekh1ZoGytvP3d8+jJwcD5yYOVakRo4B+IcttqvSFgTyAc912G4Z7kb9/sUZdME=');
        $supplierLotter->setConnectedSupplier($connectedSupplierLotter);
        $manager->persist($supplierLotter);
        
        $supplierReisser = new Supplier('Reisser DO', $company);
        $supplierReisser->setWebShopLogin('SG');
        $supplierReisser->setwebShopPassword('qWZ97JLkIQ9iZg9cHOiOC7eXAA+TSH0ycGw6vQShQyQgr1kp8hEmGGKPKuT7TLheYOAyK8cVoA==');
        $supplierReisser->setConnectedSupplier($connectedSupplierReisser);
        $supplierReisser->setCustomerNumber('30037530');
        $manager->persist($supplierReisser);
        
        $supplierPM = new Supplier('PFEIFFER & MAY DO', $company);
        $supplierPM->setWebShopLogin('kontakt@steffengrell.de');
        $supplierPM->setwebShopPassword('cEsuHodPHfEbtiTdr7sh+KWE/yzf+gTB6qfNTZGWIRxaFBWYyLUTVSDSXHNKAFiy7MIbZmLZ0Q==');
        $supplierPM->setConnectedSupplier($connectedSupplierPM);
        $manager->persist($supplierPM);
    
        $supplierGC = new Supplier('GC DO', $company);
        $supplierGC->setWebShopLogin('LIGLagerbestellung');
        $supplierGC->setwebShopPassword('JoNp9ZOzIep6dmNB8NfbGacXdQ5RfNTRxQo+urqXJELoXg9sbd3To7F4IjIArPKaveU9MvYpQxhbnuOXvohWS/JR');
        $supplierGC->setConnectedSupplier($connectedSupplierGC);
        $manager->persist($supplierGC);
    
        // CREATE FIRST MATERIAL
        
        $material1 = new Material("directOrder1", 'Bogen Profipress 28mm 90 Grad', $company);
        $manager->persist($material1);
        
        // CREATE FIRST MATERIAL ORDER SOURCES
        $orderSource1Lotter = new OrderSource('VI241628', 1, $material1, $supplierLotter, $company);
        $manager->persist($orderSource1Lotter);
        
        $orderSource1Reisser = new OrderSource('VX2/714', 1, $material1, $supplierReisser, $company);
        $manager->persist($orderSource1Reisser);

        $orderSource1PM = new OrderSource('PPB28', 1, $material1, $supplierPM, $company);
        $manager->persist($orderSource1PM);
        
        $orderSource1GC = new OrderSource('PPB28', 1, $material1, $supplierGC, $company);
        $manager->persist($orderSource1GC);
        
        // CREATE SECOND MATERIAL
    
        $material2 = new Material("directOrder2", 'Stossverbinder 0,5-1qmm E-Cu verzinnt Isolierhülse: PE rot', $company);
        $manager->persist($material2);
        
        // CREATE SECOND MATERIAL ORDER SOURCES
        
        $orderSource2GC = new OrderSource('QSVI1RTK', 1, $material2, $supplierGC, $company);
        $manager->persist($orderSource2GC);
        
        // CREATE THIRD MATERIAL
    
        $material3 = new Material("directOrder3", 'Hahnverlängerung 1/2" x 80mm DVGW Rotguss m.konischem AG u.zylindr.IG', $company);
        $manager->persist($material3);
        
        // CREATE THIRD MATERIAL ORDER SOURCES
        
        $orderSource3Lotter = new OrderSource('HVR15080', 1, $material3, $supplierLotter, $company);
        $manager->persist($orderSource3Lotter);
    
        $orderSource3PM = new OrderSource('HVR1580D', 1, $material3, $supplierPM, $company);
        $manager->persist($orderSource3PM);
    
        $orderSource3GC = new OrderSource('HVR1580D', 1, $material3, $supplierGC, $company);
        $manager->persist($orderSource3GC);
        
        // CREATE DIRECT ORDER
        
        $directOrder = new DirectOrder($supplierGC, 1, $company->getUsers()->first());
        $manager->persist($directOrder);
        
        $directOrderPosition1 = new DirectOrderPosition($directOrder, 'PPB28', 5);
        $manager->persist($directOrderPosition1);
        $directOrderPosition2 = new DirectOrderPosition($directOrder, 'QSVI1RTK', 3);
        $manager->persist($directOrderPosition2);
        $directOrderPosition3 = new DirectOrderPosition($directOrder, 'HVR1580D', 1);
        $manager->persist($directOrderPosition3);
        
        $directOrderPositionResult1_1 = new DirectOrderPositionResult($orderSource1GC, $directOrderPosition1);
        $manager->persist($directOrderPositionResult1_1);
        $directOrderPositionResult1_2 = new DirectOrderPositionResult($orderSource1Lotter, $directOrderPosition1);
        $manager->persist($directOrderPositionResult1_2);
        $directOrderPositionResult1_3 = new DirectOrderPositionResult($orderSource1Reisser, $directOrderPosition1);
        $manager->persist($directOrderPositionResult1_3);
        $directOrderPositionResult1_4 = new DirectOrderPositionResult($orderSource1PM, $directOrderPosition1);
        $manager->persist($directOrderPositionResult1_4);
        
        $directOrderPositionResult2_1 = new DirectOrderPositionResult($orderSource2GC, $directOrderPosition2);
        $manager->persist($directOrderPositionResult2_1);
        
        $directOrderPositionResult3_1 = new DirectOrderPositionResult($orderSource3GC, $directOrderPosition3);
        $manager->persist($directOrderPositionResult3_1);
        $directOrderPositionResult3_2 = new DirectOrderPositionResult($orderSource3PM, $directOrderPosition3);
        $manager->persist($directOrderPositionResult3_2);
        $directOrderPositionResult3_3 = new DirectOrderPositionResult($orderSource3Lotter, $directOrderPosition3);
        $manager->persist($directOrderPositionResult3_3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }
}
