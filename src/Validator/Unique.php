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
class Unique extends Constraint
{
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    /**
     * @Required
     * @var string
     */
    public $entity;
    
    /**
     * @Required
     * @var string[]
     */
    public $properties;
    
    /**
     * @var string
     */
    public $targets;
    
    public string $message = 'Wert muss einmalig sein.';
}
