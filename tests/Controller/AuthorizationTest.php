<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Material;
use App\Entity\PermissionGroup;
use App\Entity\User;
use App\Repository\MaterialRepository;
use App\Repository\PermissionGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthorizationTest extends WebTestCase
{
    use FixturesTrait;
    
    private KernelBrowser $client;
    
    protected function setUp(): void
    {
        $this->client = static::createClient([]);
        $this->loadFixtures([AppFixtures::class]);
    }
    
    public function testInvalidAuthorizationReadMaterial(): void
    {
        $userRepository = static::$container->get('doctrine')->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);

        $token = new JWTUserToken($user->getRoles(), $user);

        $tokenStorage = static::$container->get(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $this->client->request('GET', '/api/materials/');

        static::assertResponseStatusCodeSame(403);
    }
    
    public function testPutMaterialWithPermission(): void
    {
        $userRepository = static::$container->get('doctrine')->getRepository(User::class);
    
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::$container
            ->get('doctrine')
            ->getManager();
        
        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);

        $token = new JWTUserToken($user->getRoles(), $user);

        $tokenStorage = static::$container->get(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['name' => 'testPutMaterialLocationDublicateMainLocation']);

        /** @var PermissionGroup $permissionGroup */
        $permissionGroup = static::$container
            ->get(PermissionGroupRepository::class)
            ->findOneBy(['name' => 'matPermGroupTest']);

        $material->setPermissionGroup($permissionGroup);
        
        $entityManager->flush();

        $changes = [
            'name' => '123name',
            'orderStatus' => 'toOrder'
        ];

        $this->client->request('PUT', '/api/materials/' . $material->getId(), [], [], [], json_encode($changes));

        static::assertResponseIsSuccessful();

        /** @var Material $putMaterial */
        $putMaterial = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['name' => '123name']);

        static::assertInstanceOf(Material::class, $putMaterial);

        static::assertStringContainsString('4', $putMaterial->getitemNumber());
        static::assertStringContainsString('123name', $putMaterial->getName());
        static::assertEquals(null, $putMaterial->getManufacturerNumber());
        static::assertEquals(null, $putMaterial->getManufacturerName());
        static::assertEquals(null, $putMaterial->getBarcode());
        static::assertEquals(null, $putMaterial->getUnit());
        static::assertEquals(null, $putMaterial->getUnitAlt());
        static::assertEquals(null, $putMaterial->getOrderAmount());
        static::assertEquals(null, $putMaterial->getUsableTill());
        static::assertEquals(null, $putMaterial->getUnitConversion());
        static::assertEquals('toOrder', $putMaterial->getOrderStatus());
        static::assertEquals(null, $putMaterial->getNote());
        static::assertEquals(null, $putMaterial->getSellingPrice());
        static::assertEquals(null, $putMaterial->getItemGroup());
    }
    
    public function testInvalidPutMaterialDifferentCompany(): void
    {
        $userRepository = static::$container->get('doctrine')->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane2.doe@example.com']);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::$container
            ->get('doctrine')
            ->getManager();
        
        $token = new JWTUserToken($user->getRoles(), $user);

        $tokenStorage = static::$container->get(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        /** @var Material $material */
        $material = static::$container
            ->get(MaterialRepository::class)
            ->findOneBy(['name' => 'testPutMaterialLocationDublicateMainLocation']);

        /** @var PermissionGroup $permissionGroup */
        $permissionGroup = static::$container
            ->get(PermissionGroupRepository::class)
            ->findOneBy(['name' => 'matPermGroupTest']);

        $material->setPermissionGroup($permissionGroup);
        $entityManager->flush();

        $changes = [
            'itemNumber' => '123itemNumber',
            'name' => '123name',
            'orderStatus' => 'toOrder'
        ];

        $this->client->request('PUT', '/api/materials/' . $material->getId(), [], [], [], json_encode($changes));

        static::assertResponseStatusCodeSame(403);
    }
}
