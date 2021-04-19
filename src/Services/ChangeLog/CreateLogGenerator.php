<?php

declare(strict_types=1);


namespace App\Services\ChangeLog;


use App\Entity\ChangeLog;
use App\Entity\Data\ChangeAction;
use App\Entity\LoggableInterface;
use App\Event\Log;
use App\Services\CurrentUserProvider;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class CreateLogGenerator implements ChangeLogGeneratorInterface
{
    
    private CurrentUserProvider $currentUserProvider;
    private EntityManagerInterface $entityManager;
    private Reader $reader;
    private PropertyAccessorInterface $propertyAccessor;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        PropertyAccessorInterface $propertyAccessor,
        Reader $reader,
        EntityManagerInterface $entityManager
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->entityManager = $entityManager;
        $this->reader = $reader;
        $this->propertyAccessor = $propertyAccessor;
    }
    
    public function supports(string $action, object $entity): bool
    {
        return 'create' === $action;
    }
    
    public function getChangeLogs(object $entity): iterable
    {
        $changes = [];
        $class = get_class($entity);
        $currentUser = $this->currentUserProvider->getAuthenticatedUser();
        $properties = (new ReflectionClass($entity))->getProperties();
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $reflProperty = new ReflectionProperty($class, $propertyName);
            $propertyAnnotations = $this->reader->getPropertyAnnotations($reflProperty);
            foreach ($propertyAnnotations as $propertyAnnotation) {
                if ($propertyAnnotation instanceof Log) {
                    $value = $this->propertyAccessor->getValue($entity, $propertyName);
                    if (!$value) {
                        continue;
                    }
                    if ($value instanceof LoggableInterface) {
                        $value = $value->getLogData();
                    }
                    $change = new ChangeLog(
                        $currentUser->getCompany()->getId(),
                        $currentUser->getId(),
                        $class,
                        ChangeAction::create(),
                        $entity->getId(),
                        $propertyName,
                        (string) $value
                    );
                    $changes[] = $change;
                }
            }
        }
        return $changes;
    }
}
