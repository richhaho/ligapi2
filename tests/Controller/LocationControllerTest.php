<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Data\LocationCategory;
use App\Entity\Location;
use App\Entity\Material;
use App\Entity\MaterialLocation;
use App\Entity\User;
use App\Repository\LocationRepository;
use App\Repository\MaterialLocationRepository;
use App\Repository\MaterialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LocationControllerTest extends WebTestCase
{
    use FixturesTrait;
    use LoginTrait;
    
    private KernelBrowser $client;
    
    protected function setUp(): void
    {
        $this->client = static::createClient([]);
        $this->loadFixtures([AppFixtures::class]);
        $this->loginProgrammatically(static::$container);
    }
    
    public function testCreateMaterialWithLocation(): void
    {
        $payload = '{
            "itemNumber": "M-002",
            "locationName": "testLoc",
            "currentStock": 1,
            "currentStockAlt": 2,
            "name": "testname",
            "minStock": 4
        }';
        $this->client->request('POST', '/api/materials/?origin=test1', [], [], [], $payload);

        static::assertResponseIsSuccessful();

        /** @var MaterialLocation $createdLocationLink */
        $createdLocationLink = static::$container
            ->get(MaterialLocationRepository::class)
            ->findOneBy(['origin' => 'test1']);

        static::assertInstanceOf(MaterialLocation::class, $createdLocationLink);

        $material = $createdLocationLink->getMaterial();

        /** @var MaterialLocation[] $materialLocations */
        $foundMaterialLocations = static::$container
            ->get(MaterialLocationRepository::class)
            ->getMaterialLocationsOfMaterial($material->getId());

        static::assertInstanceOf(Material::class, $material);
        static::assertEquals('testLoc', $foundMaterialLocations[0]->getLocation()->getName());
        static::assertEquals(1, $foundMaterialLocations[0]->getCurrentStock());
        static::assertEquals(2, $foundMaterialLocations[0]->getCurrentStockAlt());
        static::assertEquals(4, $foundMaterialLocations[0]->getMinStock());
    }

    public function testAddLocationToMateriall(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $materialLocationsCount = count($material->getMaterialLocations());
        
        $allLocations = static::$container
            ->get(LocationRepository::class)
            ->findAll();
        
        $allLocationsCount = count($allLocations);

        $payload = '{
            "name": "postTestAfter",
            "minStock": 11,
            "currentStock": 12,
            "currentStockAlt": 13,
            "locationCategory": "additional"
        }';

        $url = sprintf('/api/materials/%s/locations?origin=test', $material->getId());
        $this->client->request('POST', $url, [], [], [], $payload);

        static::assertResponseIsSuccessful();

        /** @var MaterialLocation[] $materialLocations */
        $materialLocations = static::$container
            ->get(MaterialLocationRepository::class)
            ->getMaterialLocationsOfMaterial($material->getId());

        $materialLocationsCountNew = count($materialLocations);

        static::assertEquals($materialLocationsCount + 1, $materialLocationsCountNew);
    
        $allLocations = static::$container
            ->get(LocationRepository::class)
            ->findAll();
        
        static::assertEquals($allLocationsCount + 1, count($allLocations));

        /** @var MaterialLocation $materialLocation */
        $materialLocation = static::$container
            ->get(MaterialLocationRepository::class)
            ->findOneBy(["material" => $material, "locationCategory.value" => "additional"]);

        static::assertInstanceOf(MaterialLocation::class, $materialLocation);
        static::assertEquals('postTestAfter', $materialLocation->getLocation()->getName());
    }

    public function testAddLocationToMaterialMainCategoryDublicate(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $payload = '{
            "locationName": "postTestAfter",
            "minStock": 11,
            "currentStock": 12,
            "currentStockAlt": 13,
            "origin": "test",
            "locationCategory": "main"
        }';

        $url = sprintf('/api/materials/%s/locations?origin=test', $material->getId());
        $this->client->request('POST', $url, [], [], [], $payload);

        static::assertResponseStatusCodeSame(400);

        static::assertStringContainsString('Main material location already exists.', $this->client->getResponse()->getContent());
    }

    public function testPutMaterialLocationn(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::$container
            ->get('doctrine')
            ->getManager();

        $userRepository = static::$container->get('doctrine')->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);
        $company = $user->getCompany();

        $material = new Material('locPutTestMaterial', 'testname', $company, 'test');
        $entityManager->persist($material);

        $location = Location::forCompany('postTestAfter', $company);
        $entityManager->persist($location);

        $materialLocation = new MaterialLocation(LocationCategory::main(), $material, $location, $company, 'test');
        $materialLocation->setMinStock(1);
        $materialLocation->setCurrentStock(2);
        $materialLocation->setCurrentStockAlt(3);
        $entityManager->persist($materialLocation);

        $entityManager->flush();

        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('locPutTestMaterial');

        /** @var MaterialLocation[] $foundMaterialLocation */
        $foundMaterialLocations = static::$container
            ->get(MaterialLocationRepository::class)
            ->getMaterialLocationsOfMaterial($material->getId());

        $payload = '{
            "name": "putTestAfter",
            "minStock": 11,
            "currentStock": 12,
            "currentStockAlt": 13,
            "locationCategory": "main"
        }';

        $url = sprintf('/api/materials/%s/locations/%s?origin=test', $material->getId(), $foundMaterialLocations[0]->getId());
        $this->client->request('PUT', $url, [], [], [], $payload);

        static::assertResponseIsSuccessful();

        /** @var MaterialLocation $foundMaterialLocation */
        $foundMaterialLocations = static::$container
            ->get(MaterialLocationRepository::class)
            ->getMaterialLocationsOfMaterial($material->getId())[0];

        static::assertInstanceOf(Material::class, $material);
        static::assertEquals('putTestAfter', $foundMaterialLocations->getLocation()->getName());
        static::assertEquals(11, $foundMaterialLocations->getMinStock());
        static::assertEquals(12, $foundMaterialLocations->getCurrentStock());
        static::assertEquals(13, $foundMaterialLocations->getCurrentStockAlt());

    }

    public function testPutMaterialLocationDublicateMainLocation(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['name' => 'testPutMaterialLocationDublicateMainLocation']);

        /** @var MaterialLocation $foundMaterialLocation */
        $foundMaterialLocation = static::$container
            ->get(MaterialLocationRepository::class)
            ->findOneBy(['material' => $material, 'locationCategory.value' => 'additional']);

        $payload = '{
            "locationCategory": "main",
            "locationName": "another location",
            "origin": "test"
        }';

        $url = sprintf('/api/materials/%s/locations/%s?origin=test', $material->getId(), $foundMaterialLocation->getId());
        $this->client->request('PUT', $url, [], [], [], $payload);

        static::assertResponseStatusCodeSame(400);

        static::assertStringContainsString('Main material location already exists.', $this->client->getResponse()->getContent());
    }

    public function testGetMainLocationOfFixture(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $mainMaterialLocation = $material->getMainLocationLink();

        static::assertInstanceOf(MaterialLocation::class, $mainMaterialLocation);
    }

    public function testDeleteLocationFromMaterial(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $mainMaterialLocation = $material->getMainLocationLink();

        $locationId = $mainMaterialLocation->getLocation()->getId();
        $materialLocationId = $mainMaterialLocation->getId();

        $url = sprintf('/api/materials/%s/locations/%s?origin=test', $material->getId(), $materialLocationId);
        $this->client->request('DELETE', $url, [], [], []);

        static::assertResponseIsSuccessful();

        /** @var Location $location */
        $location = static::$container
            ->get(LocationRepository::class)
            ->find($locationId);

        static::assertInstanceOf(Location::class, $location);

        /** @var Location $location */
        $materialLocation = static::$container
            ->get(MaterialLocationRepository::class)
            ->find($materialLocationId);

        static::assertNull($materialLocation);

        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $mainMaterialLocation = $material->getMainLocationLink();

        static::assertNull($mainMaterialLocation);
    }
    
}
