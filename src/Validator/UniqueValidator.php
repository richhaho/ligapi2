<?php

declare(strict_types=1);


namespace App\Validator;


use App\Entity\DeleteUpdateAwareInterface;
use App\Services\CurrentUserProvider;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueValidator extends ConstraintValidator
{
    private ManagerRegistry $managerRegistry;
    private CurrentUserProvider $currentUserProvider;
    
    
    public function __construct(ManagerRegistry $managerRegistry, CurrentUserProvider $currentUserProvider)
    {
        $this->managerRegistry = $managerRegistry;
        $this->currentUserProvider = $currentUserProvider;
    }
    
    public function validate($value, Constraint $constraint): void
    {
        $entity = $constraint->entity;
        $properties = $constraint->properties;
        $targets = $constraint->targets ?? $constraint->properties;
        
        $company = $this->currentUserProvider->getAuthenticatedUser()->getCompany();
    
        /** @var EntityRepository $repository */
        $repository = $this->managerRegistry->getRepository($entity);
        
        $foundEntityQuery = $repository->createQueryBuilder('e')
            ->andWhere('e.company = :companyId')
            ->setParameter('companyId', $company->getId())
        ;
        
        if ($entity instanceof DeleteUpdateAwareInterface) {
            $foundEntityQuery->andWhere('e.deleted = false');
        }
    
        foreach ($properties as $index => $property) {
            $target = $property;
            if (isset($targets[$index])) {
                $target  = $targets[$index];
            }
            if (!isset($value->$property)) {
                return;
            }
            $foundEntityQuery->andWhere("e." . $target . " = '" . $value->$property . "'"); // TODO: Mit property binding. Problem: Nur letzter Wert wird genommen
        }
        
        if (isset($value->id)) {
            $foundEntityQuery
                ->andWhere('e.id != :id')
                ->setParameter('id', $value->id);
            ;
        }
        
        $foundEntity = $foundEntityQuery->getQuery()
            ->getResult()
        ;
        
        if ($foundEntity !== null && $foundEntity !== []) {
            $this->context->buildViolation($constraint->message)
                ->atPath($properties[0])
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setParameter('{{ entity }}', $this->formatValue($entity))
                ->setParameter('{{ property }}', $this->formatValue($properties[0]))
                ->addViolation();
        }
    }
}
