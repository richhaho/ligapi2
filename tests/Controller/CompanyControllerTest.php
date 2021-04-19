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

class CompanyControllerTest extends WebTestCase
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
    
//    public function testPutCompany(): void // TODO:...
//    {
//        $keyy = new Keyy('putTestNumber', $companyLocation, $companyLocation, $company, 'test');
//        $keyy->setAddress('Address');
//
//        $entityManager->persist($keyy);
//        $entityManager->flush();
//
//        /** @var Keyy $flushedKeyy */
//        $flushedKeyy = $keyyRepository->findOneBy(['itemNumber'=>'putTestNumber']);
//
//        $changes = [
//            'id' => $flushedKeyy->getId(),
//            'itemNumber' => 'newitemNumber',
//            'home' => 'newHome',
//            'owner' => 'newOwner',
//            'origin' => 'putTest',
//            'amount' => 1
//        ];
//
//        $this->client->request('PUT', '/api/keyys/' . $flushedKeyy->getId(), [], [], [], json_encode($changes));
//
//        static::assertResponseIsSuccessful();
//
//        /** @var Keyy $putKeyy */
//        $putKeyy = static::$container
//            ->get(KeyyRepository::class)
//            ->findOneBy(['itemNumber' => 'newitemNumber']);
//
//        static::assertInstanceOf(Keyy::class, $putKeyy);
//        static::assertNull($putKeyy->getAddress());
//        static::assertStringNotContainsString('putTestNumber', $putKeyy->getitemNumber());
//        static::assertStringContainsString('newitemNumber', $putKeyy->getitemNumber());
//    }
    
}
