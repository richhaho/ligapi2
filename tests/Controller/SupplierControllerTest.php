<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Supplier;
use App\Repository\SupplierRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SupplierControllerTest extends WebTestCase
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
        $this->client->request('GET', '/api/suppliers');

        static::assertResponseIsSuccessful();
    }

    public function testCreateSupplier(): void
    {
        $payload = '{"name": "Testlieferant", "origin": "test"}';
        $this->client->request('POST', '/api/suppliers', [], [], [], $payload);

        static::assertResponseIsSuccessful();

        $supplier = static::$container
            ->get(SupplierRepository::class)
            ->findOneBy(['name' => 'Testlieferant']);

        static::assertInstanceOf(Supplier::class, $supplier);
    }

    public function testInvalidCreateSupplier(): void
    {
        $payload = '{"name": ""}';
        $this->client->request('POST', '/api/suppliers', [], [], [], $payload);

        static::assertResponseStatusCodeSame(400);
    }
    
    public function testInvalidCreateSupplierDuplicateName(): void
    {
        $payload = '{"name": "fixture Supplier", "origin": "test"}';
        $this->client->request('POST', '/api/suppliers', [], [], [], $payload);
        
        static::assertResponseStatusCodeSame(400);
        static::assertStringContainsString('name', $this->client->getResponse()->getContent());
        static::assertStringContainsString('Wert muss einmalig sein', $this->client->getResponse()->getContent());
    }
    
    public function testPutSupplier(): void
    {
        /** @var Supplier $supplier */
        $supplier = static::$container
            ->get(SupplierRepository::class)
            ->findOneBy(['name' => 'fixture Supplier']);
        
        $payload = '{
            "websShopLogin": "123",
            "name": "newname",
            "customerNumber": "newcustomerNumber",
            "webShopLogin": "newwebsShopLogin",
            "responsiblePerson": "newresponsiblePerson",
            "street": "newstreet",
            "zipCode": "newzipCode",
            "city": "newcity",
            "email": "newemail",
            "phone": "newphone",
            "fax": "newfax"
        }';
        $this->client->request('PUT', '/api/suppliers/' . $supplier->getId(), [], [], [], $payload);
        
        static::assertResponseIsSuccessful();
    
        /** @var Supplier $updatedSupplier */
        $updatedSupplier = static::$container
            ->get(SupplierRepository::class)
            ->find($supplier->getId());

        static::assertInstanceOf(Supplier::class, $updatedSupplier);
        
        static::assertStringContainsString('newname', $updatedSupplier->getName());
        static::assertStringContainsString('newcustomerNumber', $updatedSupplier->getCustomerNumber());
        static::assertStringContainsString('newwebsShopLogin', $updatedSupplier->getWebShopLogin());
        static::assertStringContainsString('newresponsiblePerson', $updatedSupplier->getResponsiblePerson());
        static::assertStringContainsString('newstreet', $updatedSupplier->getStreet());
        static::assertStringContainsString('newzipCode', $updatedSupplier->getZipCode());
        static::assertStringContainsString('newcity', $updatedSupplier->getCity());
        static::assertStringContainsString('newemail', $updatedSupplier->getEmail());
        static::assertStringContainsString('newphone', $updatedSupplier->getPhone());
        static::assertStringContainsString('newfax', $updatedSupplier->getFax());
    }
    
    public function testDeleteSupplier(): void
    {
        /** @var Supplier $supplier */
        $supplier = static::$container
            ->get(SupplierRepository::class)
            ->findOneBy(['name' => 'fixture Supplier']);

        $this->client->request('DELETE', '/api/suppliers/' . $supplier->getId());

        static::assertResponseIsSuccessful();

        /** @var Supplier $supplier */
        $supplier = static::$container
            ->get(SupplierRepository::class)
            ->find($supplier->getId());

        static::assertEquals(null, $supplier);
    }
}
