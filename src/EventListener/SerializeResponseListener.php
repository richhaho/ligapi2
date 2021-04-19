<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Serializer\SerializerInterface;

class SerializeResponseListener implements EventSubscriberInterface
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onControllerResponse(ViewEvent $e): void
    {
        $response = JsonResponse::fromJsonString(
            $this->serializer->serialize(
                $e->getControllerResult(),
                'json'
            )
        );

        $e->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => 'onControllerResponse'
        ];
    }
}
