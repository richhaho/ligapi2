<?php


namespace App\Tests\Controller;


use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

trait LoginTrait
{
    public function loginProgrammatically($container)
    {
        $userRepository = $container->get('doctrine')->getRepository(User::class);
    
        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'steffen.grell@lagerimgriff.de']);
    
        $token = new JWTUserToken($user->getRoles(), $user);
    
        $tokenStorage = $container->get(TokenStorageInterface::class);
        $tokenStorage->setToken($token);
    
        $entityManager = static::$container->get('doctrine')->getManager();
        $filter = $entityManager->getFilters()->getFilter('company');
        $companyId = $user->getCompany()->getId();
        $filter->setParameter('company_id', $companyId);
    }
}
