<?php

declare(strict_types=1);


namespace App\Validator;


use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class GlobalUniqueValidator extends ConstraintValidator
{
    
    private ManagerRegistry $managerRegistry;
    
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
    
    public function validate($value, Constraint $constraint)
    {
        $companyFilterWasEnabled = false;
        if ($this->managerRegistry->getManager()->getFilters()->isEnabled('company')) {
            $companyFilterWasEnabled = true;
            $this->managerRegistry->getManager()->getFilters()->disable('company');
        }
        
        $entity = $constraint->entity;
        $property = $constraint->property;
        $target = $constraint->target ?? $constraint->property;
    
        /** @var EntityRepository $repository */
        $repository = $this->managerRegistry->getRepository($entity);
        
        $foundEntityQuery = $repository->createQueryBuilder('e')
            ->where('e.' . $target . ' = :value')
            ->setParameter('value', $value->$property);
        
        if (isset($value->id)) {
            $foundEntityQuery
                ->andWhere('e.id != :id')
                ->setParameter('id', $value->id);
            ;
        }
        
        $foundEntity = $foundEntityQuery->getQuery()
            ->getOneOrNullResult()
        ;
    
        if ($companyFilterWasEnabled) {
            $this->managerRegistry->getManager()->getFilters()->enable('company');
        }
        
        if ($foundEntity !== null) {
            $this->context->buildViolation($constraint->message)
                ->atPath($property)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setParameter('{{ entity }}', $this->formatValue($entity))
                ->setParameter('{{ property }}', $this->formatValue($property))
                ->addViolation();
        }
    }
}
