<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exceptions\Api\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\ConstraintViolation;

class ValidationExceptionListener implements EventSubscriberInterface
{
    private bool $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function onValidationException(ExceptionEvent $e): void
    {
        if (!$e->isMasterRequest()) {
            return;
        }

        if (!$e->getThrowable() instanceof ValidationException) {
            return;
        }

        $violations = [];
        /** @var ConstraintViolation $violation */
        foreach ($e->getThrowable()->getViolations() as $violation) {
            $violations[$violation->getPropertyPath()] = $violation->getMessage();
        }
        
        $e->stopPropagation();

        $e->setResponse(new JsonResponse(
            [
                'errors' => $violations
            ],
            Response::HTTP_BAD_REQUEST
        ));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => ['onValidationException', -12]
        ];
    }
}
