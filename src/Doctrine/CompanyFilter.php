<?php

declare(strict_types=1);


namespace App\Doctrine;


use App\Entity\CompanyAwareInterface;
use App\Entity\User;
use App\Exceptions\Domain\InconsistentDataException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class CompanyFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if (!$targetEntity->getReflectionClass()->implementsInterface(CompanyAwareInterface::class)) {
            return '';
        }
        
        if (!$this->hasParameter('company_id')) {
            if ($targetEntity->getName() !== User::class) { // TODO: find other way to avoid authorization interference
                throw InconsistentDataException::forDataIsMissing('company ID for ' . $targetEntity->getName());
            }
            return '';
        }

        return $targetTableAlias.'.company_id = ' . $this->getParameter('company_id');
    }
}
