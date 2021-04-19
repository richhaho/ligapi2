<?php

declare(strict_types=1);


namespace App\Validator;


use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS"})
 *
 */
class GlobalUnique extends Constraint
{
    public function getTargets()
    {
        return [static::CLASS_CONSTRAINT];
    }
    
    /**
     * @Required
     * @var string
     */
    public $entity;
    
    /**
     * @Required
     * @var string
     */
    public $property;
    
    /**
     * @var string
     */
    public $target;
    
    public string $message = 'Wert muss einmalig sein.';
}
