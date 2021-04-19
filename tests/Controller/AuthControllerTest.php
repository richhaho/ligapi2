<?php

declare(strict_types=1);


namespace App\Tests\Controller;


use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    use FixturesTrait;
    use LoginTrait;
    
    private KernelBrowser $client;
    
    protected function setUp(): void
    {
        $this->client = static::createClient([]);
        $this->loadFixtures([AppFixtures::class]);
    }
    
    public function testRegistration(): void
    {
        $payload = [
            'password' => '12345678',
            'company' => [
                'name' => 'Test Registrierung',
                'termsAccepted' => true
            ],
            'firstName' => 'Steff',
            'lastName' => 'Gre',
            'username' => 'steffGre',
            'email' => 'steffen@steffengrell.de'
        ];
        $this->client->request('POST', '/api/auth/register', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();

        /** @var User $user */
        $user = static::$container
            ->get(UserRepository::class)
            ->findOneBy(['firstName' => 'Steff']);

        static::assertInstanceOf(User::class, $user);

        static::assertEquals('Steff', $user->getFirstName());
        static::assertEquals('Gre', $user->getLastName());
        static::assertEquals('steffen@steffengrell.de', $user->getEmail());
        static::assertEquals(true, $user->isAdmin());
        static::assertEquals('Test Registrierung', $user->getCompany()->getName());
    }
    
    public function testLogin(): void
    {
        $payload = [
            'email' => 'steffen.grell@lagerimgriff.de',
            'password' => 'test123'
        ];
        
        $payload = json_encode($payload);
    
        $this->client->request('POST', '/api/auth/login?source=webapp', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        
        static::assertResponseIsSuccessful();
        static::assertStringContainsString('token', $this->client->getResponse()->getContent());
        static::assertStringContainsString('Steffen', $this->client->getResponse()->getContent());
        static::assertStringContainsString('Grell', $this->client->getResponse()->getContent());
        static::assertStringContainsString('Lager im Griff', $this->client->getResponse()->getContent());
    }
    
    public function testLogout(): void
    {
        /** @var User $user */
        $user = static::$container
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'steffen.grell@lagerimgriff.de']);
        
        static::assertEquals('12345', $user->getWebRefreshToken());
    
        $payload = [
            'refreshToken' => '12345'
        ];
        $this->client->request('POST', '/api/auth/logout?source=webapp', [], [], [], json_encode($payload));
    
        /** @var User $updatedUser */
        $updatedUser = static::$container
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'steffen.grell@lagerimgriff.de']);
    
        static::assertResponseIsSuccessful();
        static::assertNull($updatedUser->getWebRefreshToken());
    }
    
    public function testRefreshToken(): void
    {
        /** @var User $user */
        $user = static::$container
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'steffen.grell@lagerimgriff.de']);

        static::assertEquals('12345', $user->getWebRefreshToken());

        $payload = [
            'refreshToken' => '12345'
        ];
        $this->client->request('POST', '/api/auth/refresh?source=webapp', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();
        static::assertStringContainsString('token', $this->client->getResponse()->getContent());

        /** @var User $updatedUser */
        $updatedUser = static::$container
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'steffen.grell@lagerimgriff.de']);

        static::assertNotEquals('test1234', $updatedUser->getWebRefreshToken());
    }
}
