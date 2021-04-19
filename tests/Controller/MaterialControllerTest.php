<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\ItemGroup;
use App\Entity\Material;
use App\Entity\MaterialLocation;
use App\Entity\Supplier;
use App\Entity\User;
use App\Repository\ItemGroupRepository;
use App\Repository\MaterialRepository;
use App\Repository\SupplierRepository;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MaterialControllerTest extends WebTestCase
{
    use FixturesTrait;
    use LoginTrait;
    
    private KernelBrowser $client;
    
    protected function setUp(): void
    {
        $this->client = static::createClient([]);
        $command = 'mysql -u root -proot lig2test < var/db/test_db.sql';
        shell_exec($command);
        $this->loginProgrammatically(static::$container);
    }
    
    public function testIndex(): void
    {
        $this->client->request('GET', '/api/materials/');
        static::assertResponseIsSuccessful();
    }

    public function testCreateMateriall(): void
    {
        $payload = '{
            "itemNumber": "foobarMaterial",
            "name": "foobarMaterial"
        }';
        $this->client->request('POST', '/api/materials/?origin=test', [], [], [], $payload);

        static::assertResponseIsSuccessful();

        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['itemNumber' => 'foobarMaterial']);

        static::assertInstanceOf(Material::class, $material);
    }
    
    public function testCreateMaterialFull(): void
    {
        /** @var Supplier $supplier */
        $supplier = static::$container
            ->get(SupplierRepository::class)
            ->findOneBy(['name' => 'GC']);
        
        /** @var ItemGroup $itemGroup */
        $itemGroup = static::$container
            ->get(ItemGroupRepository::class)
            ->findOneBy(['name' => 'matGroup 1']);
        
        $payload = [
            "name" => "nnname",
                "permanentInventory" => true,
                "itemNumber" => "nnnummer",
                "materialLocations" => [
                    [
                        "name" => "lllagerort",
                        "minStock" => 12,
                        "currentStock" => 19.31,
                        "currentStockAlt" => 18.31
                    ]
                ],
                "orderSources" => [
                    [
                        "supplier" => [
                            "id" => $supplier->getId()
                        ],
                        "orderNumber" => "bbbestellnummer",
                        "amountPerPurchaseUnit" => 12.31,
                        "price" => 10.31,
                    ]
                ],
                "manufacturerNumber" => "hhherstellernummer",
                "manufacturerName" => "hhhersteller",
                "barcode" => "bbbarcode",
                "unit" => "eeeinheit",
                "unitAlt" => "fffüllmenge einheit",
                "orderAmount" => 17.31,
                "unitConversion" => 16.31,
                "note" => "nnnotiz",
                "usableTill" => "2020-08-13",
                "sellingPrice" => 15.31,
                "itemGroup" => [
                    "id" => $itemGroup->getId()
                ]
        ];
        $this->client->request('POST', '/api/materials/?origin=testneu', [], [], [], json_encode($payload));
        
        static::assertResponseIsSuccessful();
    
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['name' => 'nnname']);
        
        $locations = $material->getMaterialLocations();
        foreach ($locations as $location) {
            static::assertEquals('lllagerort', $location->getName()); // TODO: Warum locations leer?
        }
        
        static::assertInstanceOf(Material::class, $material);
        static::assertEquals('nnname', $material->getName());
        static::assertEquals(true, $material->usesPermanentInventory());
        static::assertEquals('nnnummer', $material->getItemNumber());
        static::assertEquals('lllagerort', $material->getMainLocationLink()->getName());
        static::assertEquals(19.31, $material->getMainLocationLink()->getCurrentStock());
        static::assertEquals(18.31, $material->getMainLocationLink()->getCurrentStockAlt());
        static::assertEquals(12, $material->getMainLocationLink()->getMinStock());
        static::assertEquals($supplier->getId(), $material->getMainOrderSource()->getSupplierId());
        static::assertEquals('bbbestellnummer', $material->getMainOrderSource()->getOrderNumber());
        static::assertEquals(12.31, $material->getMainOrderSource()->getAmountPerPurchaseUnit());
        static::assertEquals(10.31, $material->getMainOrderSource()->getPrice());
        static::assertEquals('hhherstellernummer', $material->getManufacturerNumber());
        static::assertEquals('hhhersteller', $material->getManufacturerName());
        static::assertEquals('bbbarcode', $material->getBarcode());
        static::assertEquals('eeeinheit', $material->getUnit());
        static::assertEquals('fffüllmenge einheit', $material->getUnitAlt());
        static::assertEquals(17.31, $material->getOrderAmount());
        static::assertEquals(16.31, $material->getUnitConversion());
        static::assertEquals('nnnotiz', $material->getNote());
        static::assertEquals('2020-08-13', $material->getUsableTill());
        static::assertEquals(15.31, $material->getSellingPrice());
        static::assertEquals('matGroup 1', $material->getItemGroup()->getName());
    }

    public function testCreateMaterialAutoNumber(): void
    {
        $payload = [
            "note" => "autoNumber",
            "name" => "autoNumber"
        ];

        $this->client->request('POST', '/api/materials/?origin=test', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();

        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['note' => 'autoNumber']);

        static::assertInstanceOf(Material::class, $material);
    }

    public function testInvalidCreateMaterial(): void
    {
        $payload = [
            "itemNumber" => ""
        ];

        $this->client->request('POST', '/api/materials/?origin=test', [], [], [], json_encode($payload));

        static::assertResponseStatusCodeSame(400);
    }

    public function testCreateMaterialWithOrderStatus(): void
    {
        $payload = [
            "itemNumber" => "foobarMaterial",
            "name" => "test",
            "orderStatus" => "toOrder"
        ];

        $this->client->request('POST', '/api/materials/?origin=test', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();

        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['itemNumber' => 'foobarMaterial']);

        static::assertInstanceOf(Material::class, $material);
        static::assertInstanceOf(DateTimeImmutable::class, $material->getOrderStatusChangeDateToOrder());
    }

    public function testCreateMaterialWithItemGroup(): void {
        $payload =
            [
            "itemNumber" => "foobarMaterial111",
            "name" => "test",
            "itemGroup" => [
                "name" => "matGroup 1"
            ]
        ];
        $this->client->request('POST', '/api/materials/?origin=test', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();

        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['itemNumber' => 'foobarMaterial111']);

        static::assertInstanceOf(Material::class, $material);
        static::assertInstanceOf(ItemGroup::class, $material->getItemGroup());
        static::assertEquals('matGroup 1', $material->getItemGroup()->getName());
    }

    public function testPutMaterial1(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['name' => 'testPutMaterialLocationDublicateMainLocation']);

        $changes = [
            'name' => '123name',
            'manufacturerNumber' => '1233manufacturerNumber',
            'manufacturerName' => '1233manufacturerName',
            'barcode' => '123barcode',
            'unit' => '123unit',
            'unitAlt' => '123unitAlt',
            'orderAmount' => 99,
            'usableTill' => '30.11.1998',
            'unitConversion' => 100,
            'orderStatus' => 'toOrder',
            'note' => '123note',
            'sellingPrice' => 19.1,
            'itemGroup' => [
                'name' => 'neue Gruppe'
            ]
        ];

        $this->client->request('PUT', '/api/materials/' . $material->getId(), [], [], [], json_encode($changes));

        static::assertResponseIsSuccessful();

        /** @var Material $putMaterial */
        $putMaterial = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['barcode' => '123barcode']);

        static::assertInstanceOf(Material::class, $putMaterial);

        static::assertStringContainsString('123name', $putMaterial->getName());
        static::assertStringContainsString('1233manufacturerNumber', $putMaterial->getManufacturerNumber());
        static::assertStringContainsString('1233manufacturerName', $putMaterial->getManufacturerName());
        static::assertStringContainsString('123barcode', $putMaterial->getBarcode());
        static::assertStringContainsString('123unit', $putMaterial->getUnit());
        static::assertStringContainsString('123unitAlt', $putMaterial->getUnitAlt());
        static::assertEquals(99, $putMaterial->getOrderAmount());
        static::assertEquals('neue Gruppe', $putMaterial->getItemGroup()->getName());
        static::assertStringContainsString('1998-11-30', $putMaterial->getUsableTill());
        static::assertEquals(100, $putMaterial->getUnitConversion());
        static::assertStringContainsString('toOrder', $putMaterial->getOrderStatus());
        static::assertStringContainsString('123note', $putMaterial->getNote());
        static::assertEquals(19.1, $putMaterial->getSellingPrice());
        static::assertStringContainsString('neue Gruppe', $putMaterial->getItemGroup()->getName());
    }

    public function testArchiveMaterial(): void
    {
        /** @var Material[] $materials */
        $materials = static::$container
            ->get(MaterialRepository::class)
            ->findAllActiveMaterials([]);

        $materialCount = count($materials);

        $materialToBeArchived = $materials[0];

        $this->client->request('GET', '/api/materials/' . $materialToBeArchived->getId() . '/archive');

        static::assertResponseIsSuccessful();

        /** @var Material[] $materials */
        $materials = static::$container
            ->get(MaterialRepository::class)
            ->findAllActiveMaterials([]);

        $newMaterialCount = count($materials);

        static::assertEquals($materialCount - 1, $newMaterialCount);

        /** @var Material $archivedMaterial */
        $archivedMaterial = static::$container
            ->get(MaterialRepository::class)
            ->find($materialToBeArchived->getId());

        static::assertEquals(true, $archivedMaterial->getIsArchived());
    }

    public function testDeleteMaterial(): void
    {
        /** @var Material[] $materials */
        $materials = static::$container
            ->get(MaterialRepository::class)
            ->findAllActiveMaterials([]);

        $materialCount = count($materials);

        $materialToBeDeleted = $materials[0];

        $this->client->request('DELETE', '/api/materials/' . $materialToBeDeleted->getId());

        static::assertResponseIsSuccessful();

        /** @var Material[] $materials */
        $materials = static::$container
            ->get(MaterialRepository::class)
            ->findAllActiveMaterials([]);

        $newMaterialCount = count($materials);

        static::assertEquals($materialCount - 1, $newMaterialCount);

        /** @var Material $deletedMaterial */
        $deletedMaterial = static::$container
            ->get(MaterialRepository::class)
            ->find($materialToBeDeleted->getId());

        static::assertEquals(null, $deletedMaterial);
    }

    public function testCreateFirstMaterialAutoitemNumber(): void
    {
        $userRepository = static::$container->get('doctrine')->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane2.doe@example.com']);

        $token = new JWTUserToken($user->getRoles(), $user);

        $tokenStorage = static::$container->get(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $payload = '{
            "origin": "test",
            "name": "test",
            "note": "testCreateFirstMaterialAutoitemNumber"
        }';
        $this->client->request('POST', '/api/materials/', [], [], [], $payload);

        static::assertResponseIsSuccessful();

        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['note' => 'testCreateFirstMaterialAutoitemNumber']);

        static::assertInstanceOf(Material::class, $material);
        static::assertEquals(1, $material->getitemNumber());
    }
    
    public function testPatchMany(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['name' => 'testPutMaterialLocationDublicateMainLocation']);
    
        /** @var MaterialLocation $firstLocation */
        $firstLocation = $material->getMaterialLocations()[0];
        
        $payload = ["data" => [
            [
                "id" => $material->getId(),
                "name" => "new name"
            ],
            [
                "id" => $material->getId(),
                "materialLocations" => [
                    [
                        "id" => $firstLocation->getId(),
                        "name" => "new location name"
                    ]
                ]
            ]
        ]];
    
        $this->client->request('POST', '/api/materials/manyrequest', [], [], [], json_encode($payload));
        static::assertResponseIsSuccessful();
    
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['name' => 'new name']);
    
        static::assertInstanceOf(Material::class, $material);
        static::assertEquals("new location name", $material->getMaterialLocations()[0]->getName());
    }
}
