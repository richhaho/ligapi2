<?php

declare(strict_types=1);


namespace App\EventListener;


use App\Entity\ChangeLog;
use App\Entity\DeleteUpdateAwareInterface;
use App\Event\ChangeEvent;
use App\EventListener\AdditionalChangeProcessors\AdditionalChangeProcessorInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChangeListener implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    
    private iterable $changeLogGenerators;
    private iterable $additionalChangeProcessors;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        iterable $changeLogGenerators,
        iterable $additionalChangeProcessors
    )
    {
        $this->entityManager = $entityManager;
        $this->changeLogGenerators = $changeLogGenerators;
        $this->additionalChangeProcessors = $additionalChangeProcessors;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            ChangeEvent::class => 'onChange'
        ];
    }
    
    public function onChange(ChangeEvent $event)
    {
        $object = $event->getObject();
        $action = $event->getAction();
        
        $changeLogs = [];
        
        foreach ($this->changeLogGenerators as $changeLogGenerator) {
            if ($changeLogGenerator->supports($action->getValue(), $object)) {
                $changeLogs += $changeLogGenerator->getChangeLogs($object);
            }
        }
        
        /** @var ChangeLog $changeLog */
        foreach($changeLogs as $changeLog) {
            $this->entityManager->persist($changeLog);
        }
    
        /** @var AdditionalChangeProcessorInterface $additionalChangeProcessor */
        foreach ($this->additionalChangeProcessors as $additionalChangeProcessor) {
            if ($additionalChangeProcessor->supports($object, $action, $changeLogs)) {
                $additionalChangeProcessor->apply($object, $action, $changeLogs);
            }
        }
    }
}
