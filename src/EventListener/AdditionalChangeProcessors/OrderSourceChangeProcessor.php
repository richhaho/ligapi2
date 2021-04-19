<?php

declare(strict_types=1);


namespace App\EventListener\AdditionalChangeProcessors;


use App\Entity\ChangeLog;
use App\Entity\Data\ChangeAction;
use App\Entity\Material;
use App\Entity\MaterialForWeb;
use App\Entity\OrderSource;
use App\Entity\PriceUpdate;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\MaterialForWebRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RequestContext;

class OrderSourceChangeProcessor implements AdditionalChangeProcessorInterface
{
    private EntityManagerInterface $entityManager;
    private RequestContext $requestContext;
    private MaterialForWebRepository $materialForWebRepository;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        RequestContext $requestContext,
        MaterialForWebRepository $materialForWebRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->requestContext = $requestContext;
        $this->materialForWebRepository = $materialForWebRepository;
    }
    
    
    /**
     * @param ChangeLog[] $changeLogs
     */
    public function supports(object $object, ChangeAction $action, array $changeLogs): bool
    {
        if (!$object instanceof OrderSource) {
            return false;
        }
    
        foreach ($changeLogs as $changeLog) {
            if ($changeLog->getProperty() === 'price' || $changeLog->getProperty() === 'amountPerPurchasingUnit') {
                return true;
            }
        }
        
        return false;
    }
    
    private function removeMaterialForWebOfMaterial(Material $material): void
    {
        $materialForWeb = $this->materialForWebRepository->findByMaterial($material);
        if (!$materialForWeb) {
            throw MissingDataException::forEntityNotFound($material->getId(), MaterialForWeb::class);
        }
        $this->entityManager->remove($materialForWeb);
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
     * @param ChangeLog[] $changeLogs
     * @param OrderSource $object
     */
    public function apply(object $object, ChangeAction $action, array $changeLogs): void
    {
        $source = '?';
        if ($this->requestContext->getParameter('changeSource')) {
            $source = $this->requestContext->getParameter('changeSource');
        }
        
        $priceUpdate = new PriceUpdate($object, $source);
        $this->entityManager->persist($priceUpdate);
        
//        $this->updateMaterialForWeb($object->getMaterial());
    }
}
