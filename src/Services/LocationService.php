<?php


namespace App\Services;


use App\Entity\Company;
use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class LocationService
{
    private LocationRepository $locationRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private array $storedLocations;
    
    public function __construct(
        LocationRepository $locationRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->locationRepository = $locationRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->storedLocations = [];
    }
    
    public function mapStringToLocation(string $name, Company $company): Location
    {
        /** @var Location $storedLocation */
        foreach ($this->storedLocations as $storedLocation) {
            if ($storedLocation->getName() === $name) {
                $this->entityManager->persist($storedLocation);
                return $storedLocation;
            }
        }
        
        $location = $this->locationRepository->findOneByName($name);
        
        if ($location) {
            $this->storedLocations[] = $location;
            return $location;
        }
        
        $existingUser = $this->userRepository->findByFullName($name);
        if ($existingUser) {
            $location = Location::forUser($existingUser);
        } else {
            $location = Location::forCompany($name, $company);
        }
        $this->entityManager->persist($location);
    
        $this->storedLocations[] = $location;
        
        return $location;
    }
}
