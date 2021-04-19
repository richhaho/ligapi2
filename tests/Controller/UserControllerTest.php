<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Data\Permission;
use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserControllerTest extends WebTestCase
{
    use FixturesTrait;
    use LoginTrait;
    use AdditionalAssertsTrait;
    
    private KernelBrowser $client;
    
    protected function setUp(): void
    {
        $this->client = static::createClient([]);
        $this->loadFixtures([AppFixtures::class]);
        $this->loginProgrammatically(static::$container);
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/users');

        static::assertResponseIsSuccessful();
    }

    public function testCreateUser(): void
    {
        $payload = [
            "firstName" => "Steffen",
            "lastName" => "Grell",
            "email" => "kontakt@lager-im-griff.de",
            "isAdmin" => true,
            "password" => "12345678",
            "permissions" => [
                [
                    "action" => "DELETE",
                    "category" => "App\Entity\Material"
                ],
                [
                    "action" => "DELETE",
                    "category" => "App\Entity\Keyy"
                ],
                [
                    "action" => "DELETE",
                    "category" => "App\Entity\Tool"
                ]
            ]
        ];
        $this->client->request('POST', '/api/users', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();

        /** @var User $user */
        $user = static::$container
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'kontakt@lager-im-griff.de']);

        static::assertInstanceOf(User::class, $user);
        static::assertEquals('Steffen', $user->getFirstName());
        static::assertEquals('Grell', $user->getLastName());
        static::assertEquals('kontakt@lager-im-griff.de', $user->getEmail());
        static::assertEquals(true, $user->isAdmin());
        foreach ($user->getPermissions() as $permission) {
            self::assertInstanceOf(Permission::class, $permission);
        }
        $permissionToTestMaterial = new Permission("App\Entity\Material", "DELETE");
        $permissionToTestTool = new Permission("App\Entity\Tool", "DELETE");
        $permissionToTestKeyy = new Permission("App\Entity\Keyy", "DELETE");
        $this->assertTrue($this->assertArrayContainsSameObject($user->getPermissions(), $permissionToTestMaterial));
        $this->assertTrue($this->assertArrayContainsSameObject($user->getPermissions(), $permissionToTestTool));
        $this->assertTrue($this->assertArrayContainsSameObject($user->getPermissions(), $permissionToTestKeyy));
    }

    public function testInvalidCreateUser(): void
    {
        $payload = '{"email": ""}';
        $this->client->request('POST', '/api/users', [], [], [], $payload);

        static::assertResponseStatusCodeSame(400);
    }

    public function testPutUser(): void
    {
        /** @var User $putUser */
        $putUser = static::$container
            ->get(UserRepository::class)
            ->findOneBy(['firstName' => 'John']);

        $changes = [
            'firstName' => 'newUserNumber',
            'lastName' => 'putTest',
            'email' => 'logins@steffengrell.de'
        ];

        $this->client->request('PUT', '/api/users/' . $putUser->getId(), [], [], [], json_encode($changes));

        static::assertResponseIsSuccessful();

        /** @var User $putUser */
        $putUser = static::$container
            ->get(UserRepository::class)
            ->find($putUser->getId());

        static::assertInstanceOf(User::class, $putUser);
        static::assertFalse($putUser->isAdmin());
        static::assertEquals([], $putUser->getPermissions());
        static::assertEquals('newUserNumber', $putUser->getFirstName());
        static::assertEquals('putTest', $putUser->getLastName());
        static::assertEquals('logins@steffengrell.de', $putUser->getEmail());
    }

    public function testDeleteUser(): void
    {
        /** @var User $user */
        $user = static::$container
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'jane.doe@example.com']);

        $this->client->request('DELETE', '/api/users/' . $user->getId());

        static::assertResponseIsSuccessful();

        /** @var User $deletedUser */
        $deletedUser = static::$container
            ->get(UserRepository::class)
            ->find($user->getId());

        static::assertEquals(null, $deletedUser);
    }

    public function testInvalidCreateUserDuplicateEmail(): void
    {
        $payload = '{
            "firstName": "Steffen",
            "lastName": "Grell",
            "email": "jane.doe@example.com",
            "password": "12345678"
        }';
        $this->client->request('POST', '/api/users', [], [], [], $payload);

        static::assertResponseStatusCodeSame(400);
    }

    public function testInvalidCreateUserDuplicateNameSameCompany(): void
    {
        $payload = '{
            "firstName": "John",
            "lastName": "Doe",
            "email": "jane123.doe@example.com",
            "password": "12345678"
        }';
        $this->client->request('POST', '/api/users', [], [], [], $payload);

        static::assertResponseStatusCodeSame(400);
    }

    public function testCreateUserSameNameDifferentCompany(): void
    {
        $userRepository = static::$container->get('doctrine')->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'admin.doe@example.com']);

        $token = new JWTUserToken($user->getRoles(), $user);

        $tokenStorage = static::$container->get(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $payload = '{
            "firstName": "John",
            "lastName": "Doe",
            "email": "john123.doe@example.com",
            "password": "12345678"
        }';
        $this->client->request('POST', '/api/users', [], [], [], $payload);

        static::assertResponseIsSuccessful();
    }
    
    public function testDeleteUserEnsureThatTaskHasResponsible()
    {
        // TODO: implementieren
    }
}
