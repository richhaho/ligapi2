<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Consignment;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\ConsignmentRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConsignmentControllerTest extends WebTestCase
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
        $this->client->request('GET', '/api/consignments/');

        static::assertResponseIsSuccessful();
    }

    public function testCreateConsignmentWithName(): void
    {
        $payload = [
            'name' => 'consignment test',
            'note' => 'Consignment Nore',
            'location' => 'New Location'
        ];
        $this->client->request('POST', '/api/consignments/', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();

        /** @var Consignment $consignment */
        $consignment = static::$container
            ->get(ConsignmentRepository::class)
            ->findOneBy(['name' => 'consignment test']);

        static::assertInstanceOf(Consignment::class, $consignment);
        static::assertEquals('Consignment Nore', $consignment->getNote());
        static::assertEquals('New Location', $consignment->getLocation());
        static::assertEquals(false, $consignment->getLocationObject()->isPersonal());
    }

    public function testCreateConsignmentWithProject(): void
    {
        /** @var ProjectRepository $projectRepository */
        $projectRepository = static::$container->get('doctrine')->getRepository(Project::class);
        /** @var Project $project */
        $project = $projectRepository->findByName('new Project');

        $payload = [
            'projectName' => $project->getName(),
            'note' => 'Project Kommission Notiz',
            'location' => 'Jane Doe'
        ];
        
        $this->client->request('POST', '/api/consignments/', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();

        /** @var Consignment $consignment */
        $consignment = static::$container
            ->get(ConsignmentRepository::class)
            ->findOneBy(['note' => 'Project Kommission Notiz']);

        static::assertInstanceOf(Consignment::class, $consignment);
        static::assertEquals('new Project', $consignment->getName());
        static::assertEquals('Project Kommission Notiz', $consignment->getNote());
        static::assertEquals('Jane Doe', $consignment->getLocationObject()->getName());
        static::assertEquals('Jane', $consignment->getLocationObject()->getUser()->getFirstName());
        static::assertEquals(true, $consignment->getLocationObject()->isPersonal());
    }

    public function testCreateConsignmentWithUser(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get('doctrine')->getRepository(User::class);
        /** @var User $user */
        $user = $userRepository->findOneBy(['firstName' => 'Steffen', 'lastName' => 'Grell']);
        
        $payload = [
            'userFullName' => $user->getFullName(),
            'note' => 'User Kommission Notiz'
        ];
        
        $this->client->request('POST', '/api/consignments/', [], [], [], json_encode($payload));

        static::assertResponseIsSuccessful();

        /** @var Consignment $consignment */
        $consignment = static::$container
            ->get(ConsignmentRepository::class)
            ->findOneBy(['note' => 'User Kommission Notiz']);

        static::assertInstanceOf(Consignment::class, $consignment);
        static::assertEquals('User Kommission Notiz', $consignment->getNote());
        static::assertEquals('Steffen Grell', $consignment->getName());
        static::assertEquals(0, $consignment->getOpenConsignmentItemsAmount());
    }

    public function testInvalidCreateConsignment(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get('doctrine')->getRepository(User::class);
        /** @var User $user */
        $user = $userRepository->findOneBy(['firstName' => 'Steffen', 'lastName' => 'Grell']);
    
        $payload = [
            'userFullName' => $user->getFullName(),
            'name' => 'test',
            'note' => 'User Kommission'
        ];
        $this->client->request('POST', '/api/consignments/', [], [], [], json_encode($payload));

        static::assertResponseStatusCodeSame(400);
        static::assertStringContainsString('Multiple identifiers set. One of them', $this->client->getResponse()->getContent());
    }
    
    public function testPutConsignmentWithName(): void
    {
        /** @var ConsignmentRepository $consignmentRepository */
        $consignmentRepository = static::$container->get('doctrine')->getRepository(Consignment::class);
        
        $initialConsignment = $consignmentRepository->findOneBy(['name' => 'Test Kommission']);

        $changes = [
            'id' => $initialConsignment->getId(),
            'name' => 'Test Kommission 2',
            'note' => 'Test Kommission Notiz 2',
            'consignmentItemStatus' => 'completed'
        ];

        $this->client->request('PUT', '/api/consignments/' . $initialConsignment->getId(), [], [], [], json_encode($changes));

        static::assertResponseIsSuccessful();

        /** @var Consignment $putConsignment */
        $putConsignment = static::$container
            ->get(ConsignmentRepository::class)
            ->find($initialConsignment->getId());

        static::assertInstanceOf(Consignment::class, $putConsignment);
        static::assertStringContainsString('Test Kommission 2', $putConsignment->getName());
        static::assertStringContainsString('Test Kommission Notiz 2', $putConsignment->getNote());
        static::assertEquals(3, $putConsignment->getOpenConsignmentItemsAmount());
    }
    
    public function testInvalidPutConsignmentWithProjectToName(): void
    {
        /** @var ProjectRepository $projectRepository */
        $projectRepository = static::$container->get('doctrine')->getRepository(Project::class);
        /** @var Project $project */
        $project = $projectRepository->findByName('new Project');
        
        /** @var ConsignmentRepository $consignmentRepository */
        $consignmentRepository = static::$container->get('doctrine')->getRepository(Consignment::class);
        
        $initialConsignment = $consignmentRepository->findOneBy(['project' => $project->getId()]);

        $changes = [
            'id' => $initialConsignment->getId(),
            'name' => 'Test Kommission 2',
            'note' => 'Test Kommission Notiz 2'
        ];

        $this->client->request('PUT', '/api/consignments/' . $initialConsignment->getId(), [], [], [], json_encode($changes));
    
        static::assertResponseStatusCodeSame(400);
        static::assertStringContainsString('Consignment name cannot be changed. User or Project connected', $this->client->getResponse()->getContent());
    }

    public function testDeleteConsignment(): void
    {
        /** @var Consignment[] $consignments */
        $consignments = static::$container
            ->get(ConsignmentRepository::class)
            ->findAll();

        $consignmentCount = count($consignments);

        $consignmentToBeDeleted = $consignments[0];

        $this->client->request('DELETE', '/api/consignments/' . $consignmentToBeDeleted->getId());

        static::assertResponseIsSuccessful();

        /** @var Consignment[] $consignments */
        $consignments = static::$container
            ->get(ConsignmentRepository::class)
            ->findAll();

        $newConsignmentCount = count($consignments);

        static::assertEquals($consignmentCount - 1, $newConsignmentCount);

        /** @var Consignment $deletedConsignment */
        $deletedConsignment = static::$container
            ->get(ConsignmentRepository::class)
            ->find($consignmentToBeDeleted->getId());

        static::assertEquals(null, $deletedConsignment);
    }
}
