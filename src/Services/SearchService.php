<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\Data\ChangeAction;
use App\Entity\SearchableInterface;
use App\Repository\SearchIndexRepository;
use Doctrine\ORM\EntityManagerInterface;

class SearchService
{
    
    private CurrentUserProvider $currentUserProvider;
    private EntityManagerInterface $entityManager;
    private SearchIndexRepository $searchIndexRepository;
    
    public function __construct(CurrentUserProvider $currentUserProvider, EntityManagerInterface $entityManager, SearchIndexRepository $searchIndexRepository)
    {
    
        $this->currentUserProvider = $currentUserProvider;
        $this->entityManager = $entityManager;
        $this->searchIndexRepository = $searchIndexRepository;
    }
    
    private function onCreate(SearchableInterface $object)
    {
//        $searchIndex = new SearchIndex(
//            $object
//        );
        
//        $this->entityManager->persist($searchIndex);
    }
    
    private function onUpdate(SearchableInterface $object)
    {
        $searchIndex = $this->searchIndexRepository->findSearchable($object);
        
        if (!$searchIndex) {
            $this->onCreate($object);
            return;
        }
        
        $searchIndex->setContent($object->getSearchableText());
    }
    
    public function addToSearchindex(SearchableInterface $object, ChangeAction $type)
    {
        if ($type->getValue() === ChangeAction::update()->getValue()) {
            $this->onUpdate($object);
        }
        if ($type->getValue() === ChangeAction::create()->getValue()) {
            $this->onCreate($object);
        }
    }
}
