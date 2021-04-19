<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\ChangeLog;
use App\Entity\Keyy;
use App\Entity\Location;
use App\Entity\User;
use App\Repository\ChangeLogRepository;
use App\Repository\KeyyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class KeyyControllerTest extends WebTestCase
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
    
    public function testKeyyIndex(): void
    {
        $this->client->request('GET', '/api/keyys/');

        static::assertResponseIsSuccessful();
    }

    public function testCreateKeyy(): void
    {
        $payload = '{"itemNumber": "foobarKey", "home": "originalHome", "owner": "originalOwner", "name": "hans"}';
        $this->client->request('POST', '/api/keyys/?origin=test', [], [], [], $payload);

        static::assertResponseIsSuccessful();
    
        /** @var Keyy $keyy */
        $keyy = static::$container
            ->get(KeyyRepository::class)
            ->findOneBy(['itemNumber' => 'foobarKey']);

        static::assertInstanceOf(Keyy::class, $keyy);
        static::assertEquals('hans', $keyy->getName());
        static::assertEquals('originalHome', $keyy->getHome());
        static::assertEquals('foobarKey', $keyy->getitemNumber());
    
        /** @var ChangeLog $keyyLog */
        $keyyLog = static::$container
            ->get(ChangeLogRepository::class)
            ->findOneBy(['objectId' => $keyy->getId(), 'property' => 'itemNumber']);
        
        static::assertStringContainsString('foobarKey', $keyyLog->getNewValue());
    }

    public function testInvalidCreateKeyy(): void
    {
        $payload = '{"itemNumber": ""}';
        $this->client->request('POST', '/api/keyys/?origin=postman', [], [], [], $payload);

        static::assertResponseStatusCodeSame(400);
    }
    
    public function testInvalidCreateKeyyDuplicateNumber(): void
    {
        $payload = '{"itemNumber": "K-001", "origin": "test", "home": "originalHome", "owner": "originalOwner"}';
        $this->client->request('POST', '/api/keyys/?origin=postman', [], [], [], $payload);
        
        static::assertResponseStatusCodeSame(400);
        static::assertStringContainsString('itemNumber', $this->client->getResponse()->getContent());
        static::assertStringContainsString('Wert muss einmalig sein', $this->client->getResponse()->getContent());
    }

    public function testPutKeyy(): void
    {
        $userRepository = static::$container->get('doctrine')->getRepository(User::class);
        /** @var KeyyRepository $keyyRepository */
        $keyyRepository = static::$container->get('doctrine')->getRepository(Keyy::class);
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::$container
            ->get('doctrine')
            ->getManager();

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);

        $company = $user->getCompany();

        $companyLocation = Location::forCompany('test', $company);
        $entityManager->persist($companyLocation);

        $keyy = new Keyy('putTestNumber', '123', $companyLocation, $companyLocation, $company, 'test');
        $keyy->setAddress('Address');

        $entityManager->persist($keyy);
        $entityManager->flush();

        /** @var Keyy $flushedKeyy */
        $flushedKeyy = $keyyRepository->findOneBy(['itemNumber'=>'putTestNumber']);

        $changes = [
            'id' => $flushedKeyy->getId(),
            'itemNumber' => 'newitemNumber',
            'home' => 'newHome',
            'owner' => 'newOwner',
            'origin' => 'putTest',
            'amount' => 1,
            'name' => '321'
        ];

        $this->client->request('PUT', '/api/keyys/' . $flushedKeyy->getId(), [], [], [], json_encode($changes));

        static::assertResponseIsSuccessful();

        /** @var Keyy $putKeyy */
        $putKeyy = static::$container
            ->get(KeyyRepository::class)
            ->findOneBy(['itemNumber' => 'newitemNumber']);

        static::assertInstanceOf(Keyy::class, $putKeyy);
        static::assertNull($putKeyy->getAddress());
        static::assertStringNotContainsString('putTestNumber', $putKeyy->getitemNumber());
        static::assertStringContainsString('newitemNumber', $putKeyy->getitemNumber());
        static::assertStringContainsString('321', $putKeyy->getName());
    }

    public function testArchiveKeyy(): void
    {
        /** @var Keyy[] $keyys */
        $keyys = static::$container
            ->get(KeyyRepository::class)
            ->findAllActiveKeyys();

        $keyyCount = count($keyys);

        $keyyToBeArchived = $keyys[0];

        $this->client->request('GET', '/api/keyys/' . $keyyToBeArchived->getId() . '/archive');

        static::assertResponseIsSuccessful();

        /** @var Keyy[] $keyys */
        $keyys = static::$container
            ->get(KeyyRepository::class)
            ->findAllActiveKeyys();

        $newKeyyCount = count($keyys);

        static::assertEquals($keyyCount - 1, $newKeyyCount);

        /** @var Keyy $archivedKeyy */
        $archivedKeyy = static::$container
            ->get(KeyyRepository::class)
            ->find($keyyToBeArchived->getId());

        static::assertEquals(true, $archivedKeyy->getIsArchived());
    }

    public function testDeleteKeyy(): void
    {
        /** @var Keyy[] $keyys */
        $keyys = static::$container
            ->get(KeyyRepository::class)
            ->findAllActiveKeyys();

        $keyyCount = count($keyys);

        $keyyToBeDeleted = $keyys[0];

        $this->client->request('DELETE', '/api/keyys/' . $keyyToBeDeleted->getId());

        static::assertResponseIsSuccessful();

        /** @var Keyy[] $keyys */
        $keyys = static::$container
            ->get(KeyyRepository::class)
            ->findAllActiveKeyys();

        $newKeyyCount = count($keyys);

        static::assertEquals($keyyCount - 1, $newKeyyCount);

        /** @var Keyy $deletedKeyy */
        $deletedKeyy = static::$container
            ->get(KeyyRepository::class)
            ->find($keyyToBeDeleted->getId());

        static::assertEquals(null, $deletedKeyy);
    }
    
    public function testAutoIncreaseitemNumber(): void
    {
        $payload = '{"origin": "test", "home": "zuhause", "owner": "zuhause", "address": "12345", "name": "test"}';
        $this->client->request('POST', '/api/keyys/?origin=postman', [], [], [], $payload);
        
        static::assertResponseIsSuccessful();
        
        /** @var Keyy $keyy */
        $keyy = static::$container
            ->get(KeyyRepository::class)
            ->findOneBy(['address' => '12345']);
        
        static::assertInstanceOf(Keyy::class, $keyy);
        static::assertEquals('zuhause', $keyy->getHome());
        static::assertEquals('1', $keyy->getitemNumber());
    }
}
