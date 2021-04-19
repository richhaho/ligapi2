<?php

declare(strict_types=1);


namespace App\EventListener;


use App\Entity\SearchableInterface;
use App\Event\ChangeEvent;
use App\Services\SearchService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchIndexListener implements EventSubscriberInterface
{
    private SearchService $searchService;
    
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
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
        
        if ($object instanceof SearchableInterface) {
            $this->searchService->addToSearchindex($object, $action);
        }
    }
}
