<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\ConnectedSupplier;
use App\Entity\Consignment;
use App\Entity\ConsignmentItem;
use App\Entity\Customer;
use App\Entity\CustomField;
use App\Entity\Data\CustomFieldType;
use App\Entity\Data\EntityType;
use App\Entity\Data\File;
use App\Entity\Data\ItemGroupType;
use App\Entity\Data\LocationCategory;
use App\Entity\Data\MaterialOrderType;
use App\Entity\Data\OrderStatus;
use App\Entity\Data\Permission;
use App\Entity\Data\TaskStatus;
use App\Entity\ItemGroup;
use App\Entity\Keyy;
use App\Entity\Location;
use App\Entity\Material;
use App\Entity\MaterialLocation;
use App\Entity\MaterialOrder;
use App\Entity\MaterialOrderPosition;
use App\Entity\OrderSource;
use App\Entity\PermissionGroup;
use App\Entity\Project;
use App\Entity\Supplier;
use App\Entity\Task;
use App\Entity\Tool;
use App\Security\UserService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Money\Money;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppFixtures extends Fixture
{
    private UserService $userService;
    private ParameterBagInterface $parameterBag;
    
    public function __construct(
        UserService $userService,
        ParameterBagInterface $parameterBag
    )
    {
        $this->userService = $userService;
        $this->parameterBag = $parameterBag;
    }
    
    /**
     * @param ObjectManager $manager
     * @throws Exception
     */
    public function load(ObjectManager $manager)
    {
        ini_set('memory_limit', '1024M');
        
        $company1 = new Company('Lager im Griff', true);
        $company1->setStreet("Clemensstsr. 54");
        $company1->setZip("53225");
        $company1->setCity("Bonn");
        $company1->setCountry("Deutschland");
        $company1->setPhone("0228 24033695");
        $company1->setWebsite("www.lagerimgriff.de");
        $manager->persist($company1);

        $company2 = new Company('Foo Comp', true);
        $company2->setCountry('Schweiz');
        $manager->persist($company2);

        $user1 = $this->userService->createUser('Steffen', 'Grell', 'steffen.grell@lagerimgriff.de', 'test123', $company1);
        $user1->setIsAdmin(true);
        $user1->setWebRefreshToken('12345');
        $user1->setMobileRefreshToken('12345');
        $manager->persist($user1);

        $permissionGroup = new PermissionGroup('matPermGroupTest', $company1);
        $manager->persist($permissionGroup);

        $user2 = $this->userService->createUser('Jane', 'Doe', 'jane.doe@example.com', 'test123', $company1);
        $user2->setPermissions([
            new Permission($permissionGroup->getId(), Permission::DELETE)
        ]);
        $manager->persist($user2);

        $user3 = $this->userService->createUser('Jane2', 'Doe', 'jane2.doe@example.com', 'test123', $company2);
        $user3->setPermissions([
            new Permission(Material::class, Permission::DELETE),
            new Permission(Keyy::class, Permission::DELETE),
            new Permission(Tool::class, Permission::DELETE)
        ]);
        $manager->persist($user3);

        $userAdmin = $this->userService->createUser('Ad', 'min', 'admin.doe@example.com', 'test123', $company2);
        $userAdmin->setIsAdmin(true);
        $manager->persist($userAdmin);

        $customFieldText = new CustomField($company1, 'testText', CustomFieldType::text(), EntityType::material());
        $manager->persist($customFieldText);

        $customFieldCheckbox = new CustomField($company1, 'testCheckbox', CustomFieldType::checkbox(), EntityType::material());
        $manager->persist($customFieldCheckbox);

        $customFieldSelect = new CustomField($company1, 'testSelect', CustomFieldType::select(), EntityType::material());
        $customFieldSelect->setOptions(['Option 1', 'Option 2', 'Option 3']);
        $manager->persist($customFieldSelect);

        $userLocation = Location::forUser($user1);
        $manager->persist($userLocation);

        $companyLocation = Location::forCompany( 'test', $company1);
        $manager->persist($companyLocation);

        $matGroup = new ItemGroup('matGroup 1', ItemGroupType::material(), $company1);
        $manager->persist($matGroup);

        $toolGroup = new ItemGroup('toolGroup 1', ItemGroupType::tool(), $company1);
        $manager->persist($toolGroup);

        $keyy = new Keyy('K-001', 'Eingangstüre', $companyLocation, $companyLocation, $company1);
        $manager->persist($keyy);

        $tool = new Tool('T-001', 'Hammer', $userLocation, $userLocation, $company1);
        $tool->setItemGroup($toolGroup);
        $manager->persist($tool);

        $connectedSupplier = new ConnectedSupplier('GC 1', 'https://gconlineplus.de/');
        $manager->persist($connectedSupplier);

        $supplier = new Supplier('Ordnung GmbH', $company1);
        $supplier->setStreet("Im Griff 10");
        $supplier->setZipCode("53225");
        $supplier->setCity("Bonn");
        $supplier->setResponsiblePerson("Peter Pünktlich");
        $supplier->setWebShopLogin('LIGLagerbestellung');
        $supplier->setwebShopPassword('JoNp9ZOzIep6dmNB8NfbGacXdQ5RfNTRxQo+urqXJELoXg9sbd3To7F4IjIArPKaveU9MvYpQxhbnuOXvohWS/JR');
        $supplier->setConnectedSupplier($connectedSupplier);
        $manager->persist($supplier);

        $supplier2 = new Supplier('fixture Supplier 2', $company1);
        $manager->persist($supplier2);

        $supplier3 = new Supplier('fixture Supplier 3', $company1);
        $manager->persist($supplier3);

        $customer = new Customer('testCustomer', $company1);
        $manager->persist($customer);

        $project = new Project('new Project', $company1);
        $project->setCustomer($customer);
        $manager->persist($project);

        $consignment1 = new Consignment($company1, 1, null, null, 'Test Kommission');
        $consignment1->setLocation($companyLocation);
        $manager->persist($consignment1);

        $consignment2 = new Consignment($company1, 2, null, $user1);
        $consignment2->setLocation($companyLocation);
        $manager->persist($consignment2);

        $consignment3 = new Consignment($company1, 3, $project);
        $consignment3->setLocation($userLocation);
        $manager->persist($consignment3);

        for($i = 1; $i < 2; $i++) {
            $material = new Material($i, 'Schraube ' . $i, $company1);
            $material->setItemGroup($matGroup);
            $material->setManufacturerName('TestHersteller');
            $material->setSellingPrice(Money::EUR('142'));
            $material->setPermanentInventory(true);
            $material->updateOrderStatus(OrderStatus::toOrder(), $user1);
            $material->setCustomFields([
                $customFieldText->getId() => "test Text 123",
                $customFieldCheckbox->getId() => true,
                $customFieldSelect->getId() => "Option 1"
            ]);
            $manager->persist($material);

            $materialLocation = new MaterialLocation($material->getCompany(),LocationCategory::main(), $material, $companyLocation);
            $materialLocation->setMinStock(2 + $i);
            $materialLocation->setMaxStock(10 + $i);
            $materialLocation->setCurrentStock(4 + $i);
            $materialLocation->setCurrentStockAlt(40 + 10 * $i);
            $manager->persist($materialLocation);

            $orderSource = new OrderSource('ev', 1, $material, $supplier, $company1);
            $orderSource->setPrice(13);
            $orderSource->setLastPriceUpdate(new DateTimeImmutable());
//            $orderSource->setCrawlerStatus(CrawlerStatus::new());
            $orderSource->setLastAutoSet(new DateTimeImmutable());
            $manager->persist($orderSource);

            $orderSource2 = new OrderSource('test number ' . $i, 2, $material, $supplier2, $company1);
            $manager->persist($orderSource2);
            
            $task = new Task($company1, 'test', $user1, $material);
            $manager->persist($task);
        }

        $material = new Material($i + 1, 'new GC', $company1);
        $material->setAutoSearchTerm('MEW20K');
        $material->setAutoSupplier($supplier);
//        $material->setCrawlerStatus(CrawlerStatus::new());
        $manager->persist($material);

        $consignmentItem1 = new ConsignmentItem($company1, $consignment1, $material, null, null, 3);
        $manager->persist($consignmentItem1);

        $consignmentItem2 = new ConsignmentItem($company1, $consignment1, null, $tool, null);
        $manager->persist($consignmentItem2);

        $consignmentItem3 = new ConsignmentItem($company1, $consignment1, null, null, $keyy);
        $manager->persist($consignmentItem3);

        $orderSource3 = new OrderSource('ROYALT270', 2, $material, $supplier, $company1);
        $manager->persist($orderSource3);

        $orderSource4 = new OrderSource('GS', 2, $material, $supplier, $company1);
        $manager->persist($orderSource4);

        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $companyDataDir = $projectDir . '/public/companyData/';
        $company1Dir = $companyDataDir . $company1->getId() . '/uploads';

        $file = new File('companyData/' . $company1->getId() . '/uploads/test.png', 'test.png','image/png', 1000, 'thumb', 10, 100);

        $testMaterial = new Material($i + 2, 'testPutMaterialLocationDublicateMainLocation', $company1);
        $testMaterial->addFile($file);

        $manager->persist($testMaterial);

        $testMaterialWithPermissionGroup = new Material($i + 3, 'Nägel', $company1);
        $testMaterialWithPermissionGroup->setName('testMaterialWithPermissionGroup');
        $testMaterialWithPermissionGroup->setPermissionGroup($permissionGroup);
        $manager->persist($testMaterialWithPermissionGroup);

        $testMaterialWithOrderSource = new Material($i + 4, 'Farbe', $company1);
        $manager->persist($testMaterialWithOrderSource);
        $orderSource = new OrderSource('test number', 1, $testMaterialWithOrderSource, $supplier, $company1);
        $manager->persist($orderSource);

        $testMaterialMainLocationLink = new MaterialLocation($testMaterial->getCompany(), LocationCategory::main(), $testMaterial, $companyLocation);
        $testMaterialMainLocationLink->setMinStock(3);
        $testMaterialMainLocationLink->setMaxStock(30);
        $manager->persist($testMaterialMainLocationLink);
        $testMaterialAdditionalLocationLink = new MaterialLocation($testMaterial->getCompany(), LocationCategory::additional(), $testMaterial, $userLocation);
        $manager->persist($testMaterialAdditionalLocationLink);
        
        $order = new MaterialOrder(MaterialOrderType::webshop(), $orderSource->getSupplier(), $company1, 2, 'Test Liefernotiz');
        $manager->persist($order);

        $orderPosition1 = new MaterialOrderPosition($company1, 2, $orderSource, $order);
        $manager->persist($orderPosition1);

        $orderPosition2 = new MaterialOrderPosition($company1, 2, $orderSource2, $order);
        $manager->persist($orderPosition2);

        $generalTask = new Task($company1, 'Test Aufgabe', $user1);
        $generalTask->setDetails('Details test Aufgabe');
        $generalTask->setDueDate(new DateTimeImmutable());
        $generalTask->setStartDate(new DateTimeImmutable());
        $generalTask->setPriority(2);
        $generalTask->setRepeatAfterDays(10);
        $generalTask->setTaskStatus(TaskStatus::complete());
        $manager->persist($generalTask);

        $materialTask = new Task($company1, 'Material Aufgabe', $user1, $material);
        $manager->persist($materialTask);

        $toolTask = new Task($company1, 'Werkzeug Aufgabe', $user2, null, $tool);
        $manager->persist($toolTask);

        $keyyTask = new Task($company1, 'Schlüssel Aufgabe', $user2, null, null, $keyy);
        $manager->persist($keyyTask);

        $manager->flush();

        mkdir($company1Dir, 0777,true);
        copy($projectDir . '/src/DataFixtures/test.png', $company1Dir . '/test.png');
    }
}
