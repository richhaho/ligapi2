<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Exceptions\Api\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ValidationTrait
{
    private ValidatorInterface $validator;
    
    public function validate($dto)
    {
        $violations = $this->validator->validate($dto);
    
        if (count($violations) > 0) {
            throw ValidationException::fromViolationList($violations);
        }
    }
}
