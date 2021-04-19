<?php

declare(strict_types=1);


namespace App\Controller;

use App\Api\ApiContext;
use App\Repository\OwnerChangeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/ownerchanges/", name="api_ownerchange_")
 */
class OwnerChangeChontroller
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"})
     */
    public function index(Request $request, OwnerChangeRepository $ownerChangeRepository): iterable
    {
        return $ownerChangeRepository->getWithQuery($request->query->all());
    }
}
