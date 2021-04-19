<?php

declare(strict_types=1);


namespace App\EventListener;


use App\Entity\Material;
use App\Event\ChangeEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheInvalidationListener implements EventSubscriberInterface
{
    private TagAwareCacheInterface $serializer_cache;
    private EntityManagerInterface $entityManager;
    
    public function __construct(TagAwareCacheInterface $serializer_cache, EntityManagerInterface $entityManager)
    {
        $this->serializer_cache = $serializer_cache;
        $this->entityManager = $entityManager;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            ChangeEvent::class => 'onChange'
        ];
    }
    
    public function onChange(ChangeEvent $event)
    {
        $class = get_class($event->getObject());
        
        $class = $this->entityManager->getClassMetadata($class)->getName();
        
        $action = $event->getAction();
        if ($action->getValue() === 'update' || $action->getValue() === 'delete') {
            $tag = str_replace('\\', '_',$class) . '_' . $event->getObject()->getId();
            $this->serializer_cache->invalidateTags([$tag]);
        }
        if ($action->getValue() === 'create') {
            if (method_exists($event->getObject(), 'getMaterial')) {
                /** @var Material $material */
                $material = $event->getObject()->getMaterial();
                if ($material) {
                    $tag = str_replace('\\', '_',Material::class) . '_' . $material->getId();
                    $this->serializer_cache->invalidateTags([$tag]);
                }
            }
            if (method_exists($event->getObject(), 'getMaterials')) {
                /** @var Material[] $materials */
                $materials = $event->getObject()->getMaterials();
                if (!$materials) {
                    return;
                }
                foreach ($materials as $material) {
                    $tag = str_replace('\\', '_',Material::class) . '_' . $material->getId();
                    $this->serializer_cache->invalidateTags([$tag]);
                }
            }
        }
    }
}
