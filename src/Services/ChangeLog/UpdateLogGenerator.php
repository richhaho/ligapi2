<?php

declare(strict_types=1);


namespace App\Services\ChangeLog;


use App\Entity\ChangeLog;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Keyy;
use App\Entity\LoggableInterface;
use App\Entity\Tool;
use App\Event\Log;
use App\Services\CurrentUserProvider;
use App\Services\OneSignalService;
use DateTimeImmutable;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Money\Money;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;

class UpdateLogGenerator implements ChangeLogGeneratorInterface
{
    private CurrentUserProvider $currentUserProvider;
    private EntityManagerInterface $entityManager;
    private Reader $reader;
    private Security $security;
    private OneSignalService $oneSignalService;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        EntityManagerInterface $entityManager,
        Reader $reader,
        Security $security,
        OneSignalService $oneSignalService
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->entityManager = $entityManager;
        $this->reader = $reader;
        $this->security = $security;
        $this->oneSignalService = $oneSignalService;
    }
    
    public function supports(string $action, object $entity): bool
    {
        return 'update' === $action;
    }
    
    private function checkPermission(object $subject, string $key): void
    {
        if (!$subject instanceof Tool && !$subject instanceof Keyy) {
            return;
        }
        
        if ($key === 'owner') {
            if(!$this->security->isGranted(Permission::BOOK, $subject)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
        } else {
            if(!$this->security->isGranted(Permission::EDIT, $subject)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
        }
    }
    
    public function getChangeLogs(object $entity): iterable
    {
        $changes = [];
        
        $class = get_class($entity);
        $currentUser = $this->currentUserProvider->getAuthenticatedUser();
        if ($currentUser) {
            $currentUserId = $currentUser->getId();
            $currentCompanyId = $currentUser->getCompany()->getId();
        } else {
            $currentUserId = 'System';
            $currentCompanyId = 'System';
        }
        $this->entityManager->getUnitOfWork()->computeChangeSets();
        $properties = (new ReflectionClass($entity))->getProperties();
        $unitOfWork = $this->entityManager->getUnitOfWork()->getEntityChangeSet($entity);
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $reflProperty = new ReflectionProperty($class, $propertyName);
            $propertyAnnotations = $this->reader->getPropertyAnnotations($reflProperty);
            foreach ($propertyAnnotations as $propertyAnnotation) {
                if ($propertyAnnotation instanceof Log) {
                    if ($propertyAnnotation->logKey) {
                        $propertyName = $propertyAnnotation->logKey;
                    }
                    foreach ($unitOfWork as $key => $value) {
                        if ($key === $propertyName) {
                            $newValue = $value[1];
                            if ($newValue instanceof LoggableInterface) {
                                $newValue = $newValue->getLogData();
                            }
                            if ($newValue instanceof Money) {
                                $newValue = $newValue->getAmount();
                            }
                            if ($value[0] instanceof Money && !$value[0]->getAmount() && !$newValue) {
                                continue;
                            }
                            if ($newValue instanceof DateTimeImmutable) {
                                $newValue = $newValue->format('d.m.Y H:i:s');
                                if ($value[0] === $newValue) {
                                    continue;
                                }
                            }
                            if ($newValue instanceof DateTimeImmutable) {
                                $newValue = $newValue->format('d.m.Y H:i:s');
                            }
                            
                            if (is_array($newValue)) {
                                $newValue = json_encode($newValue);
                            }
                            
                            $this->checkPermission($entity, $key);
                            
                            $change = new ChangeLog(
                                $currentCompanyId,
                                $currentUserId,
                                $class,
                                ChangeAction::update(),
                                $entity->getId(),
                                $propertyName,
                                (string) $newValue
                            );
                            $changes[] = $change;
                        }
                    }
                }
            }
        }
        return $changes;
    }
}
