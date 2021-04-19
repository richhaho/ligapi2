<?php

declare(strict_types=1);


namespace App\Services\ChangeLog;


use App\Entity\ChangeLog;

interface ChangeLogGeneratorInterface
{
    public function supports(string $action, object $entity): bool;
    
    /**
     * @return ChangeLog[]
     */
    public function getChangeLogs(object $entity): iterable;
}
