<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    
    public function findByName(string $firstName, string $lastName): ?User
    {
        return $this->createQueryBuilder('user')
            ->where('user.firstName = :firstName')
            ->setParameter('firstName', $firstName)
            ->andWhere('user.lastName = :lastName')
            ->setParameter('lastName', $lastName)
            ->andWhere('user.deleted = false')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function findByUuid(string $deviceUuid): ?User
    {
        return $this->createQueryBuilder('user')
            ->where('user.deviceUuid = :deviceUuid')
            ->setParameter('deviceUuid', $deviceUuid)
            ->andWhere('user.deleted = false')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    
    public function findByFullName(string $fullName): ?User
    {
        return $this->createQueryBuilder('user')
            ->where("CONCAT(user.firstName, ' ', user.lastName) = :fullName")
            ->setParameter('fullName', $fullName)
            ->andWhere('user.deleted = false')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function findByRefreshToken(string $refreshToken, string $type): ?User
    {
        $companyFilterWasEnabled = false;
        if ($this->_em->getFilters()->isEnabled('company')) {
            $companyFilterWasEnabled = true;
            $this->_em->getFilters()->disable('company');
        }
        
        $queryBuilder = $this->createQueryBuilder('user');
        
        if ($type === 'webapp') {
            $queryBuilder
                ->andWhere('user.webRefreshToken = :refreshToken')
                ->setParameter('refreshToken', $refreshToken)
            ;
        }
        if ($type === 'mobileapp') {
            $queryBuilder
                ->andWhere('user.mobileRefreshToken = :refreshToken')
                ->setParameter('refreshToken', $refreshToken)
            ;
        }
        
        $result = $queryBuilder
            ->andWhere('user.deleted = false')
            ->getQuery()
            ->getOneOrNullResult()
            ;
        
        if ($companyFilterWasEnabled) {
            $this->_em->getFilters()->enable('company');
        }
    
        return $result;
    }
    
    public function getByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('user')
            ->where('user.email = :email')
            ->setParameter('email', $email)
            ->andWhere('user.deleted = false')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function getByResetPasswordToken(string $passwordResetToken): ?User
    {
        return $this->createQueryBuilder('user')
            ->where('user.passwordResetToken = :passwordResetToken')
            ->setParameter('passwordResetToken', $passwordResetToken)
            ->andWhere('user.deleted = false')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
