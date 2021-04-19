<?php

declare(strict_types=1);


namespace App\Validator;


use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MaterialLocationCategoryValidator extends ConstraintValidator
{
    
    private ManagerRegistry $managerRegistry;
    
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
    
    public function validate($value, Constraint $constraint)
    {
        $entity = $constraint->entity;
        $property = $constraint->properties;
        
        $repository = $this->managerRegistry->getRepository($entity);
        
        $foundEntity = $repository->findOneBy([$property => $value]);
        
        if ($foundEntity !== null) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setParameter('{{ entity }}', $this->formatValue($entity))
                ->setParameter('{{ property }}', $this->formatValue($property))
                ->addViolation();
        }
    }
}
