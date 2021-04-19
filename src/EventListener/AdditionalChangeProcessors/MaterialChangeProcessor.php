<?php

declare(strict_types=1);


namespace App\EventListener\AdditionalChangeProcessors;


use App\Entity\ChangeLog;
use App\Entity\Data\ChangeAction;
use App\Entity\Material;
use App\Entity\MaterialForWeb;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\MaterialForWebRepository;
use Doctrine\ORM\EntityManagerInterface;

class MaterialChangeProcessor implements AdditionalChangeProcessorInterface
{
    private MaterialForWebRepository $materialForWebRepository;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        MaterialForWebRepository $materialForWebRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->materialForWebRepository = $materialForWebRepository;
        $this->entityManager = $entityManager;
    }
    
    public function supports(object $object, ChangeAction $action, array $changeLogs): bool
    {
        return $object instanceof Material;
    }
    
    private function removeMaterialForWebOfMaterial(Material $material): void
    {
        $materialForWeb = $this->materialForWebRepository->findByMaterial($material);
        if ($materialForWeb) {
//            throw MissingDataException::forEntityNotFound($material->getId(), MaterialForWeb::class);
            $this->entityManager->remove($materialForWeb);
        }
    }
    
    private function createMaterialForWeb(Material $material): void
    {
        $materialForWeb = new MaterialForWeb($material);
        $this->entityManager->persist($materialForWeb);
    }
    
    /**
     * @param ChangeLog[] $changeLogs
     */
    private function updateMaterialForWeb(Material $material): void
    {
        $materialForWeb = $this->materialForWebRepository->findByMaterial($material);
        if (!$materialForWeb) {
            throw MissingDataException::forEntityNotFound($material->getId(), MaterialForWeb::class);
        }
        
        $this->removeMaterialForWebOfMaterial($material);
        $this->entityManager->flush();
        $this->createMaterialForWeb($material);
    }
    
    /**
     * @param Material $object
     */
    public function apply(object $object, ChangeAction $action, array $changeLogs): void
    {
//                $this->mercureService->sendMessage($changeLog->getObjectClass(), ["id" => $changeLog->getObjectId()]);
//        $messageSent = true;
        
//        switch ($action->getValue()) {
//            case 'create':
//                $this->createMaterialForWeb($object);
//                break;
//            case 'update':
//                $this->updateMaterialForWeb($object);
//                break;
//            case 'delete':
//                $this->removeMaterialForWebOfMaterial($object);
//                break;
//        }
    }
}
