<?php

declare(strict_types=1);


namespace App\Doctrine;


use App\Entity\DeleteUpdateAwareInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class DeletedFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (!$targetEntity->getReflectionClass()->implementsInterface(DeleteUpdateAwareInterface::class)) {
            return '';
        }

        return $targetTableAlias.'.deleted = false';
    }
}
