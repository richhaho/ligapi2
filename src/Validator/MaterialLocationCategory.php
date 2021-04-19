<?php

declare(strict_types=1);


namespace App\Validator;


use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class MaterialLocationCategory extends Constraint
{
    public string $entity;
    public string $property;
    public string $message = 'Wert muss einmalig sein.';
}
