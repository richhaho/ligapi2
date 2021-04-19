<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Consignment;
use App\Entity\ConsignmentItem;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\Tool;
use App\Repository\ConsignmentItemRepository;
use App\Repository\ConsignmentRepository;
use App\Repository\KeyyRepository;
use App\Repository\MaterialRepository;
use App\Repository\ToolRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConsignmentItemControllerTest extends WebTestCase
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

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/consignmentItems/');

        static::assertResponseIsSuccessful();
    }

    public function testCreateConsignmentItemWithMaterial(): void
    {
        /** @var Consignment $consignment */
        $consignment = static::$container
            ->get(ConsignmentRepository::class)
            ->findOneBy(['name' => 'Test Kommission']);
        
        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['name' => 'Schraube 1']);
        
        $payload = [
            'consignmentId' => $consignment->getId(),
            'materialId' => $material->getId(),
            'amount' => 10
        ];
        $this->client->request('POST', '/api/consignmentitems/', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();

        /** @var ConsignmentItem $consignmentItem */
        $consignmentItem = static::$container
            ->get(ConsignmentItemRepository::class)
            ->findOneBy(['amount' => 10]);

        static::assertInstanceOf(ConsignmentItem::class, $consignmentItem);
        static::assertEquals('Schraube 1', $consignmentItem->getLinkedItem()['name']);
        static::assertEquals(10, $consignmentItem->getAmount());
        static::assertEquals('open', $consignmentItem->getConsignmentItemStatus());
        static::assertEquals($consignment->getId(), $consignmentItem->getConsignment()->getId());
    }

    public function testCreateConsignmentItemWithTool(): void
    {
        /** @var Consignment $consignment */
        $consignment = static::$container
            ->get(ConsignmentRepository::class)
            ->findOneBy(['name' => 'Test Kommission']);
        
        /** @var Tool $tool */
        $tool = static::$container
            ->get(ToolRepository::class)
            ->findOneBy(['itemNumber' => 'T-001']);
        
        $payload = [
            'consignmentId' => $consignment->getId(),
            'toolId' => $tool->getId()
        ];
        $this->client->request('POST', '/api/consignmentitems/', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();

        /** @var ConsignmentItem $consignmentItem */
        $consignmentItem = static::$container
            ->get(ConsignmentItemRepository::class)
            ->findOneBy(['amount' => 10]);

        static::assertInstanceOf(ConsignmentItem::class, $consignmentItem);
        static::assertEquals('Hammer', $consignmentItem->getLinkedItem()['name']);
        static::assertEquals('open', $consignmentItem->getConsignmentItemStatus());
        static::assertEquals($consignment->getId(), $consignmentItem->getConsignment()->getId());
    }

    public function testCreateConsignmentItemWithKeyy(): void
    {
        /** @var Consignment $consignment */
        $consignment = static::$container
            ->get(ConsignmentRepository::class)
            ->findOneBy(['name' => 'Test Kommission']);
        
        /** @var Keyy $keyy */
        $keyy = static::$container
            ->get(KeyyRepository::class)
            ->findOneBy(['itemNumber' => 'K-001']);
        
        $payload = [
            'consignmentId' => $consignment->getId(),
            'keyyId' => $keyy->getId(),
        ];
        $this->client->request('POST', '/api/consignmentitems/', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();

        /** @var ConsignmentItem $consignmentItem */
        $consignmentItem = static::$container
            ->get(ConsignmentItemRepository::class)
            ->findOneBy(['amount' => 10]);

        static::assertInstanceOf(ConsignmentItem::class, $consignmentItem);
        static::assertEquals('Eingangstüre', $consignmentItem->getLinkedItem()['name']);
        static::assertEquals('open', $consignmentItem->getConsignmentItemStatus());
        static::assertEquals($consignment->getId(), $consignmentItem->getConsignment()->getId());
    }

    public function testInvalidCreateConsignmentItem(): void
    {
        /** @var Consignment $consignment */
        $consignment = static::$container
            ->get(ConsignmentRepository::class)
            ->findOneBy(['name' => 'Test Kommission']);
    
        $payload = [
            'consignmentId' => $consignment->getId()
        ];
        $this->client->request('POST', '/api/consignmentItems/', [], [], [], json_encode($payload));

        static::assertResponseStatusCodeSame(500);
        static::assertStringContainsString('Multiple identifiers set. One of them', $this->client->getResponse()->getContent());
    }
    
    public function testPutConsignmentItem(): void
    {
        /** @var ConsignmentItemRepository $consignmentItemRepository */
        $consignmentItemRepository = static::$container->get('doctrine')->getRepository(ConsignmentItem::class);
        
        $initialConsignmentItem = $consignmentItemRepository->findOneBy(['amount' => 3]);

        $changes = [
            'amount' => 99,
            'consignmentItemItemStatus' => 'completed',
            'consignedAmount' => 80
        ];

        $this->client->request('PUT', '/api/consignmentitems/' . $initialConsignmentItem->getId(), [], [], [], json_encode($changes));

        static::assertResponseIsSuccessful();

        /** @var ConsignmentItem $putConsignmentItem */
        $putConsignmentItem = static::$container
            ->get(ConsignmentItemRepository::class)
            ->find($initialConsignmentItem->getId());

        static::assertInstanceOf(ConsignmentItem::class, $putConsignmentItem);
        static::assertStringContainsString('completed', $putConsignmentItem->getConsignmentItemStatus());
        static::assertEquals(99, $putConsignmentItem->getAmount());
        static::assertEquals(80, $putConsignmentItem->getConsignedAmount());
    }

    public function testDeleteConsignmentItem(): void
    {
        /** @var ConsignmentItem[] $consignmentItems */
        $consignmentItems = static::$container
            ->get(ConsignmentItemRepository::class)
            ->findAll();

        $consignmentItemCount = count($consignmentItems);

        $consignmentItemToBeDeleted = $consignmentItems[0];

        $this->client->request('DELETE', '/api/consignmentitems/' . $consignmentItemToBeDeleted->getId());

        static::assertResponseIsSuccessful();

        /** @var ConsignmentItem[] $consignmentItems */
        $consignmentItems = static::$container
            ->get(ConsignmentItemRepository::class)
            ->findAll();

        $newConsignmentItemCount = count($consignmentItems);

        static::assertEquals($consignmentItemCount - 1, $newConsignmentItemCount);

        /** @var ConsignmentItem $deletedConsignmentItem */
        $deletedConsignmentItem = static::$container
            ->get(ConsignmentItemRepository::class)
            ->find($consignmentItemToBeDeleted->getId());

        static::assertEquals(null, $deletedConsignmentItem);
    }
}
