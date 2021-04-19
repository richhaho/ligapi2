<?php


namespace App\Exceptions\Domain;


class UnsupportedUserException extends \InvalidArgumentException implements DomainException
{
    public static function forUnsupportedUser($user): self
    {
        $e = new self(sprintf('Class %s is not supported. It should be App\Entity\User.', get_class($user)));
    
        return $e;
    }
}
