<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Entity\Location;
use App\Repository\LocationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/locations", name="api_location_")
 */
class LocationController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"stocktaking"}, selfRoute="api_location_get")
     * @param LocationRepository $repository
     * @return iterable
     */
    public function index(Request $request, LocationRepository $repository): iterable
    {
        $params = $request->query->all();
        return $repository->findWithParams($params);
    }
    
    /**
     * @Route(path="/{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_location_get")
     */
    public function get(Location $location): Location
    {
        return $location;
    }
}
