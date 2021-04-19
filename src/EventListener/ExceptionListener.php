<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exceptions\Domain\DomainException;
use App\Exceptions\Domain\HttpException;
use App\Exceptions\Domain\MissingDataException;
use App\Exceptions\Domain\UserReadableException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener implements EventSubscriberInterface
{
    private bool $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function onApiException(ExceptionEvent $e): void
    {
        if (!$e->isMasterRequest()) {
            return;
        }

        if (0 !== stripos($e->getRequest()->getRequestUri(), '/api')) {
            return;
        }

        $exception = $e->getThrowable();

        $message = 'Fehler! Bitte wenden Sie sich an kontakt@lagerimgriff.de';
        
        if ($exception instanceof UserReadableException) {
            $message = $exception->getUserMessage();
        } else {
            $message .= PHP_EOL . PHP_EOL . $exception->getMessage();
        }
        
        if ($exception instanceof AccessDeniedHttpException) {
            $message = 'Fehlende Berechtigung';
        }
        
//        if ($this->debug) {
//            $exception = $e->getThrowable();
//            $message = $exception->getMessage();
//        }

        $code = 500;
        if ($exception instanceof HttpExceptionInterface) {
            $code = $exception->getStatusCode();
        } else if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
        } else if ($exception instanceof MissingDataException) {
            $message = 'Daten nicht gefunden: ' . $exception->getMessage();
            $code = 404;
        }

        $e->setResponse(new JsonResponse(
            [
                'error' => $message
            ],
            $code
        ));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => ['onApiException', -16]
        ];
    }
}
