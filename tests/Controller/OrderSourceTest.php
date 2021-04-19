<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Material;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use App\Repository\MaterialRepository;
use App\Repository\OrderSourceRepository;
use App\Repository\SupplierRepository;
use DateTimeImmutable;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderSourceTest extends WebTestCase
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
    
    public function testCreateMaterialWithOrderSource(): void
    {
        /** @var Supplier $supplier */
        $supplier = static::$container
            ->get(SupplierRepository::class)
            ->findOneBy(['name' => 'fixture Supplier']);

        $payload = sprintf('{
            "itemNumber": "M-002",
            "supplierId": "%s",
            "orderNumber": "testNumber",
            "amountPerPurchaseUnit": 100,
            "origin": "test"
        }', $supplier->getId());
        $this->client->request('POST', '/api/materials', [], [], [], $payload);

        static::assertResponseIsSuccessful();

        /** @var OrderSource $orderSource */
        $orderSource = static::$container
            ->get(OrderSourceRepository::class)
            ->findOneBy(['origin' => 'test']);

        static::assertInstanceOf(OrderSource::class, $orderSource);

        $material = $orderSource->getMaterial();

        static::assertInstanceOf(Material::class, $material);
        static::assertEquals('testNumber', $orderSource->getOrderNumber());
        static::assertEquals(100, $orderSource->getAmountPerPuchaseUnit());
        static::assertEquals('fixture Supplier', $orderSource->getSupplier()->getName());
    }

    public function testAddOrderSourceToMaterial(): void
    {
        /** @var Supplier $supplier */
        $supplier = static::$container
            ->get(SupplierRepository::class)
            ->findOneBy(['name' => 'fixture Supplier 3']);

        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $materialOrderSourcesCount = count($material->getOrderSources());

        $payload = sprintf('{
            "orderNumber": "newOrderNumber",
            "supplierId": "%s",
            "priority": 2,
            "origin": "test"
        }', $supplier->getId());

        $url = sprintf('/api/materials/%s/ordersources', $material->getId());
        $this->client->request('POST', $url, [], [], [], $payload);

        static::assertResponseIsSuccessful();

        /** @var OrderSource[] $orderSources */
        $orderSources = static::$container
            ->get(OrderSourceRepository::class)
            ->getOrderSourcesOfMaterial($material);

        $materialOrderSourcesCountNew = count($orderSources);

        static::assertEquals($materialOrderSourcesCount + 1, $materialOrderSourcesCountNew);

        /** @var OrderSource $orderSource */
        $orderSource = static::$container
            ->get(OrderSourceRepository::class)
            ->findOneBy(["priority" => 2, "orderNumber" => "newOrderNumber"]);

        static::assertInstanceOf(OrderSource::class, $orderSource);
        static::assertEquals('newOrderNumber', $orderSource->getOrderNumber());
    }

    public function testInvalidAddOrderSourceToMaterialDuplicateSupplier(): void
    {
        /** @var Supplier $supplier */
        $supplier = static::$container
            ->get(SupplierRepository::class)
            ->findOneBy(['name' => 'fixture Supplier']);

        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('10');

        $payload = sprintf('{
            "orderNumber": "newOrderNumber",
            "supplierId": "%s",
            "priority": 2,
            "origin": "test"
        }', $supplier->getId());

        $url = sprintf('/api/materials/%s/ordersources', $material->getId());
        $this->client->request('POST', $url, [], [], [], $payload);

        static::assertResponseStatusCodeSame(400);
    }

    public function testPutOrderSource(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $orderSource = $material->getMainOrderSource();

        $payload = '{
            "orderNumber": "putTestAfter",
            "note": "putNote",
            "amountPerPurchaseUnit": 99,
            "priority": '.$orderSource->getPriority().',
            "origin": "test"
        }';

        $url = sprintf('/api/materials/%s/ordersources/%s', $material->getId(), $orderSource->getId());
        $this->client->request('PUT', $url, [], [], [], $payload);

        static::assertResponseIsSuccessful();

        /** @var OrderSource $orderSource */
        $orderSource = static::$container
            ->get(OrderSourceRepository::class)
            ->getOrderSourceOfMaterialAndSupplier($material, $orderSource->getSupplier());

        static::assertInstanceOf(Material::class, $material);
        static::assertEquals('putTestAfter', $orderSource->getOrderNumber());
        static::assertEquals('putNote', $orderSource->getNote());
        static::assertEquals('99', $orderSource->getAmountPerPuchaseUnit());
        static::assertEquals(null, $orderSource->getPrice());
    }

    public function testGetMainOrderSourceOfFixture(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $mainOrderSource = $material->getMainOrderSource();

        static::assertInstanceOf(OrderSource::class, $mainOrderSource);
    }

    public function testDeleteOrderSourceFromMaterial(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $mainOrderSource = $material->getMainOrderSource();

        $supplierId = $mainOrderSource->getSupplier()->getId();
        $orderSourceId = $mainOrderSource->getId();

        $url = sprintf('/api/materials/%s/ordersources/%s', $material->getId(), $orderSourceId);
        $this->client->request('DELETE', $url, [], [], []);

        static::assertResponseIsSuccessful();

        /** @var Supplier $supplier */
        $supplier = static::$container
            ->get(SupplierRepository::class)
            ->find($supplierId);

        static::assertInstanceOf(Supplier::class, $supplier);

        /** @var OrderSource $orderSource
         */
        $orderSource = static::$container
            ->get(OrderSourceRepository::class)
            ->find($orderSourceId);

        static::assertNull($orderSource);

        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $mainOrderSource = $material->getMainOrderSource();

        static::assertNull($mainOrderSource);
    }

    public function testUpdatePriceOfOrderSource(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $orderSourceId = $material->getMainOrderSource()->getId();

        /** @var OrderSource $orderSource */
        $orderSource = static::$container
            ->get(OrderSourceRepository::class)
            ->find($orderSourceId);

        $currentPrice = $orderSource->getPrice();

        self::assertEquals(13, $currentPrice);

        $payload = '{
            "price": 9995,
            "priority": '.$orderSource->getPriority().',
            "orderNumber": "'.$orderSource->getOrderNumber().'",
            "origin": "test"
        }';

        $url = sprintf('/api/materials/%s/ordersources/%s', $material->getId(), $orderSourceId );
        $this->client->request('PUT', $url, [], [], [], $payload);

        static::assertResponseIsSuccessful();

        /** @var OrderSource $updatedOrderSource */
        $updatedOrderSource = static::$container
            ->get(OrderSourceRepository::class)
            ->find($orderSource->getId());

        self::assertEquals(9995, $updatedOrderSource->getPrice());
        self::assertEquals((new DateTimeImmutable())->format('Y-m-d'), $updatedOrderSource->getLastPriceUpdate());
    }

    public function testAddOrdersourceWithPrioOne(): void
    {
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findByitemNumber('1');

        $currentPrioOneOrderSource = $material->getMainOrderSource();

        /** @var Supplier $supplier3 */
        $supplier3 = static::$container
            ->get(SupplierRepository::class)
            ->findOneBy(['name' => 'fixture Supplier 3']);

        $payload = '{
            "orderNumber": "postTestAfter",
            "priority": 1,
            "note": "postNote",
            "amountPerPurchaseUnit": 99,
            "supplierId": "'.$supplier3->getId().'",
            "origin": "test"
        }';

        $url = sprintf('/api/materials/%s/ordersources', $material->getId());
        $this->client->request('POST', $url, [], [], [], $payload);

        static::assertResponseIsSuccessful();

        /** @var OrderSource $updatedOldPrioOneOrderSource */
        $updatedOldPrioOneOrderSource = static::$container
            ->get(OrderSourceRepository::class)
            ->find($currentPrioOneOrderSource->getId());

        self::assertEquals(2, $updatedOldPrioOneOrderSource->getPriority());
    }

    public function testUpdateOrdersourceWithPrioOne(): void
    {
        /** @var OrderSource $orderSource2 */
        $orderSource2 = static::$container
            ->get(OrderSourceRepository::class)
            ->findOneBy(['orderNumber' => 'test number 2']);

        $payload = '{
            "priority": 1,
            "orderNumber": "test number 2",
            "origin": "Test"
        }';

        $prio1OrderSource = $orderSource2->getMaterial()->getMainOrderSource();

        $url = sprintf('/api/materials/%s/ordersources/%s', $orderSource2->getMaterial()->getId(), $orderSource2->getId() );
        $this->client->request('PUT', $url, [], [], [], $payload);

        static::assertResponseIsSuccessful();

        $updatedPrio1OrderSource = static::$container
            ->get(OrderSourceRepository::class)
            ->find($prio1OrderSource->getId());

        self::assertEquals(2, $updatedPrio1OrderSource->getPriority());
    }
    
}
