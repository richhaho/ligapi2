<?php

declare(strict_types=1);


namespace App\Tests\Controller;


use App\Entity\Material;
use App\Repository\MaterialRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NeedsFixingTest extends WebTestCase
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
    
    // Prepare fixtures with "APP_ENV=test bin/console app:phpunit"
    // Launch tests with "symfony php ./bin/phpunit --filter=testAddMultipleMaterials"
    
    // TODO: Challenge is: The locations need to be cached somehow.
    public function testAddMultipleMaterials(): void
    {
        $payload = [
            "material" => [
                "name" => "test multiple",
                "orderStatus" => "available",
                "permanentInventory" => true,
                "materialLocations" => [
                    [
                        "name" => "new location",
                        "locationCategory" => "main",
                        "currentStock" => 0,
                        "currentStockAlt" => 0,
                        "minStock" => 0,
                        "maxStock" => 0,
                        "material" => []
                    ]
                ],
//                "orderSources" => [
//                    [
//                        "orderNumber" => "100",
//                        "supplier" => [
//                            "id" => "8aafc851-28ec-41f2-a9d9-8461c2c75d83",
//                            "name" => ""
//                        ]
//                    ]
//                ],
//                "unit" => "t",
                "itemGroup" => [
                    "name" => "new item group"
                ],
                "customFields" => []
            ],
            "amount" => 10
        ];
        
        $this->client->request('POST', '/api/materials/multiple?origin=test', [], [], [], json_encode($payload));
        
        static::assertResponseIsSuccessful();
        
        $materials = static::$container
            ->get(MaterialRepository::class)
            ->findBy(['name' => 'test multiple']);
        
        static::assertInstanceOf(Material::class, $materials[0]);
        static::assertCount(10, $materials);
    }
    
    // TODO: After fixtures are created, test loads 5.000 entries. Should be possible within seconds at most.
    public function testIndex(): void
    {
        $this->client->request('GET', '/api/materials/');
        static::assertResponseIsSuccessful();
    }
    
    /*
     * TODO: Import Test
     * Create Test for
     * bin/console app:import
     * bin/console messenger:consume batch -vv
     */
}
