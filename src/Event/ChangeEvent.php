<?php

declare(strict_types=1);


namespace App\Event;


use App\Entity\Data\ChangeAction;
use Symfony\Contracts\EventDispatcher\Event;

class ChangeEvent extends Event
{
    private ChangeAction $action;
    private object $object;
    
    public function __construct(ChangeAction $action, object $object)
    {
        $this->action = $action;
        $this->object = $object;
    }
    
    public function getAction(): ChangeAction
    {
        return $this->action;
    }
    
    public function getObject(): object
    {
        return $this->object;
    }
    
}
