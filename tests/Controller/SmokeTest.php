<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @IgnoreAnnotation("dataProvider")
 */
class SmokeTest extends WebTestCase
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
    
    /**
     * @dataProvider provideValidPaths
     * @param string $path
     */
    public function testPageExists(string $path): void
    {
        
        $this->loginProgrammatically(static::$container);
    
        $this->client->request('GET', $path);

        static::assertResponseIsSuccessful();
    }

    public function provideValidPaths(): iterable
    {
        return [
            ['/api/keyys/'],
            ['/api/tools/'],
            ['/api/materials/'],
            ['/api/consignments/'],
            ['/api/consignmentitems/'],
            ['/api/customers/']
        ];
    }

}
