<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Company;
use App\Entity\User;
use App\Exceptions\Domain\InconsistentDataException;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    private UserPasswordEncoderInterface $encoder;
    private EntityManagerInterface $manager;
    private UserRepository $userRepository;
    
    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager, UserRepository $userRepository)
    {
        $this->encoder = $encoder;
        $this->manager = $manager;
        $this->userRepository = $userRepository;
    }
    
    public function userWithSameNameAlreadyExists(string $firstName, string $lastName): bool
    {
        return !!$this->userRepository->findByName($firstName, $lastName);
    }

    public function createUser(string $firstName, string $lastName, string $email, string $password, Company $company): User
    {
        $user = new User($firstName, $lastName, $email, $company);
        $user->updatePassword($password, $this->encoder);

        return $user;
    }
}
