<?php


namespace App\EventListener\AdditionalChangeProcessors;


use App\Entity\ChangeLog;
use App\Entity\Data\ChangeAction;

interface AdditionalChangeProcessorInterface
{
    /**
     * @param ChangeLog[] $changeLogs
     */
    public function supports(object $object, ChangeAction $action, array $changeLogs): bool;
    /**
     * @param ChangeLog[] $changeLogs
     */
    public function apply(object $object, ChangeAction $action, array $changeLogs): void;
}
