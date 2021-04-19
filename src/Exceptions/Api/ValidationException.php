<?php

declare(strict_types=1);

namespace App\Exceptions\Api;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \RuntimeException implements ApiException
{
    private ConstraintViolationListInterface $violations;

    public static function fromViolationList(ConstraintViolationListInterface $violations): self
    {
        $e = new self('Data is invalid');
        $e->violations = $violations;

        return $e;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
