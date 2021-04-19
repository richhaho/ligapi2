<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Location;
use App\Entity\Tool;
use App\Entity\User;
use App\Repository\ToolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ToolControllerTest extends WebTestCase
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
    
    private function createTool(): Tool
    {
        $userRepository = static::$container->get('doctrine')->getRepository(User::class);
        
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::$container->get('doctrine')->getManager();
    
        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);
        $company = $user->getCompany();
        $companyLocation = Location::forCompany(  'test2',$company);
        $entityManager->persist($companyLocation);
        $tool = new Tool( '100', 'testNumber', $companyLocation, $companyLocation, $company, 'test');
        $tool->setManufacturerName('manufacturer');
    
        $entityManager->persist($tool);
        $entityManager->flush();
        
        return $tool;
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/tools/');

        static::assertResponseIsSuccessful();
    }

    public function testCreateTool(): void
    {
        $payload = '{"itemNumber": "foobarTool", "origin": "test", "home": "zuhause", "owner": "zuhause"}';
        $this->client->request('POST', '/api/tools/', [], [], [], $payload);

        static::assertResponseIsSuccessful();

        /** @var Tool $tool */
        $tool = static::$container
            ->get(ToolRepository::class)
            ->findOneBy(['itemNumber' => 'foobarTool']);

        static::assertInstanceOf(Tool::class, $tool);
        static::assertEquals('zuhause', $tool->getHome());
    }

    public function testInvalidCreateTool(): void
    {
        $payload = '{"itemNumber": ""}';
        $this->client->request('POST', '/api/tools/', [], [], [], $payload);

        static::assertResponseStatusCodeSame(400);
    }
    
    public function testInvalidCreateToolDuplicateNumber(): void
    {
        $payload = '{"itemNumber": "T-001", "name": "test", "home": "zuhause", "owner": "zuhause"}';
        $this->client->enableProfiler();
        $this->client->request('POST', '/api/tools/?origin=test', [], [], [], $payload);
        $test = $this->client->getProfile()->getCollector('validator');
        static::assertResponseStatusCodeSame(400);
        static::assertStringContainsString('itemNumber', $this->client->getResponse()->getContent());
        static::assertStringContainsString('Wert muss einmalig sein', $this->client->getResponse()->getContent());
    }
    
    public function testPutTool(): void
    {
        $initialTool = $this->createTool();

        $changes = [
            'itemNumber' => 'newitemNumber',
            'origin' => 'putTest',
            'home' => 'neues Zuhause',
            'owner' => $initialTool->getOwner()
        ];

        $this->client->request('PUT', '/api/tools/' . $initialTool->getId(), [], [], [], json_encode($changes));

        static::assertResponseIsSuccessful();

        /** @var Tool $putTool */
        $putTool = static::$container
            ->get(ToolRepository::class)
            ->findOneBy(['itemNumber' => 'newitemNumber']);

        static::assertInstanceOf(Tool::class, $putTool);
        static::assertNull($putTool->getManufacturerName());
        static::assertStringNotContainsString('testNumber', $putTool->getitemNumber());
        static::assertStringContainsString('newitemNumber', $putTool->getitemNumber());
        static::assertStringContainsString('neues Zuhause', $putTool->getHome());
    }

    public function testArchiveTool(): void
    {
        /** @var Tool[] $tools */
        $tools = static::$container
            ->get(ToolRepository::class)
            ->findAllActiveTools();

        $toolCount = count($tools);

        $toolToBeArchived = $tools[0];

        $this->client->request('GET', '/api/tools/' . $toolToBeArchived->getId() . '/archive');

        static::assertResponseIsSuccessful();

        /** @var Tool[] $tools */
        $tools = static::$container
            ->get(ToolRepository::class)
            ->findAllActiveTools();

        $newToolCount = count($tools);

        static::assertEquals($toolCount - 1, $newToolCount);

        /** @var Tool $archivedTool */
        $archivedTool = static::$container
            ->get(ToolRepository::class)
            ->find($toolToBeArchived->getId());

        static::assertEquals(true, $archivedTool->getIsArchived());
    }

    public function testDeleteTool(): void
    {
        /** @var Tool[] $tools */
        $tools = static::$container
            ->get(ToolRepository::class)
            ->findAllActiveTools();

        $toolCount = count($tools);

        $toolToBeDeleted = $tools[0];

        $this->client->request('DELETE', '/api/tools/' . $toolToBeDeleted->getId());

        static::assertResponseIsSuccessful();

        /** @var Tool[] $tools */
        $tools = static::$container
            ->get(ToolRepository::class)
            ->findAllActiveTools();

        $newToolCount = count($tools);

        static::assertEquals($toolCount - 1, $newToolCount);

        /** @var Tool $deletedTool */
        $deletedTool = static::$container
            ->get(ToolRepository::class)
            ->find($toolToBeDeleted->getId());

        static::assertEquals(null, $deletedTool);
    }

    public function testCreateToolWithItemGroup(): void {
        $payload = '
            {
                "itemNumber": "foobarTool",
                "home": "zuhause",
                "owner": "zuhause",
                "itemGroup": "itemGroup 1"
            }';
        $this->client->request('POST', '/api/tools/?origin=test', [], [], [], $payload);

        static::assertResponseIsSuccessful();

        /** @var Tool $tool */
        $tool = static::$container
            ->get(ToolRepository::class)
            ->findOneBy(['itemNumber' => 'foobarTool']);

        static::assertInstanceOf(Tool::class, $tool);
        static::assertEquals('itemGroup 1', $tool->getItemGroup());
    }

    public function testCreateToolWithInvalidItemGroup(): void
    {
        $payload = '
            {
                "itemNumber": "foobarTool",
                "origin": "test",
                "home": "zuhause",
                "owner": "zuhause",
                "itemGroup": "invalid"
            }';
        $this->client->request('POST', '/api/tools', [], [], [], $payload);

        static::assertEquals(400, $this->client->getResponse()->getStatusCode());

        static::assertStringContainsString('No entity with name invalid for class App', $this->client->getResponse()->getContent());
    }
    
    public function testCreateFirstToolAutoitemNumber(): void
    {
        $userRepository = static::$container->get('doctrine')->getRepository(User::class);
        
        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane2.doe@example.com']);
        
        $token = new JWTUserToken($user->getRoles(), $user);
        
        $tokenStorage = static::$container->get(TokenStorageInterface::class);
        $tokenStorage->setToken($token);
        
        $payload = '{
            "origin": "test",
            "note": "testCreateFirstMaterialAutoitemNumber",
            "home": "test",
            "owner": "test"
        }';
        $this->client->request('POST', '/api/tools', [], [], [], $payload);
        
        static::assertResponseIsSuccessful();
        
        /** @var Tool $tool */
        $tool = static::$container
            ->get(ToolRepository::class)
            ->findOneBy(['note' => 'testCreateFirstMaterialAutoitemNumber']);
        
        static::assertInstanceOf(Tool::class, $tool);
        static::assertEquals(1, $tool->getitemNumber());
    }
    
    public function testAutoIncreaseitemNumber(): void
    {
        $payload = '{"origin": "test", "home": "zuhause", "owner": "zuhause", "barcode": "12345"}';
        $this->client->request('POST', '/api/tools', [], [], [], $payload);
    
        static::assertResponseIsSuccessful();
    
        /** @var Tool $tool */
        $tool = static::$container
            ->get(ToolRepository::class)
            ->findOneBy(['barcode' => '12345']);
    
        static::assertInstanceOf(Tool::class, $tool);
        static::assertEquals('zuhause', $tool->getHome());
        static::assertEquals('1', $tool->getitemNumber());
    }
}
