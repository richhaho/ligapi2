<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Data\ItemGroupType;
use App\Entity\ItemGroup;
use App\Entity\PermissionGroup;
use App\Entity\Supplier;
use App\Security\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CypressFixtures extends Fixture implements DependentFixtureInterface
{
    private UserService $userService;
    private ParameterBagInterface $parameterBag;
    
    public function __construct(
        UserService $userService,
        ParameterBagInterface $parameterBag
    )
    {
        $this->userService = $userService;
        $this->parameterBag = $parameterBag;
    }
    
    public function load(ObjectManager $manager)
    {
        return;
        
        $company1 = new Company('Cypress', true);
        $company1->setCountry('Deutschland');
        $manager->persist($company1);

        $user1 = $this->userService->createUser('Cy', 'Press', 'cypress@steffengrell.de', 'test123', $company1);
        $user1->setIsAdmin(true);
        $user1->setWebRefreshToken('12345');
        $user1->setMobileRefreshToken('12345');
        $manager->persist($user1);

        $permissionGroup = new PermissionGroup('matPermGroupTest', $company1);
        $manager->persist($permissionGroup);

        $matGroup = new ItemGroup('matGroup 1', ItemGroupType::material(), $company1);
        $manager->persist($matGroup);

        $toolGroup = new ItemGroup('toolGroup 1', ItemGroupType::tool(), $company1);
        $manager->persist($toolGroup);

        $supplier3 = new Supplier('fixture Supplier 3', $company1);
        $manager->persist($supplier3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            AppFixtures::class,
        ];
    }
}
