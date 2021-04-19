<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\LocationCollectionDto;
use App\Api\Dto\PutCompany;
use App\Api\Dto\PutCompanySettings;
use App\Api\Mapper\CompanyMapper;
use App\Entity\Company;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Material;
use App\Event\ChangeEvent;
use App\Services\CompanyService;
use App\Services\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/companies", name="api_companies_")
 */
class CompanyController
{
    
    /**
     * @Route(path="/userfullnames", name="get_user_fullnames", methods={"GET"})
     */
    public function get_user_fullnames(CurrentUserProvider $currentUserProvider): Response
    {
        $company = $currentUserProvider->getAuthenticatedUser()->getCompany();
        $users = $company->getFullUserNames();
        
        return new JsonResponse($users, 200);
    }
    
    /**
     * @Route(path="/own", name="get_own", methods={"GET"})
     * @ApiContext(groups={"detail"})
     */
    public function get_own(CurrentUserProvider $currentUserProvider): Company
    {
        return $currentUserProvider->getAuthenticatedUser()->getCompany();
    }
    
    /**
     * @Route(path="/locationcollection", name="get_location_collection", methods={"GET"})
     */
    public function get_location_collection(CurrentUserProvider $currentUserProvider, Request $request): Response
    {
        $company = $currentUserProvider->getAuthenticatedUser()->getCompany();
        $locationCollection = $company->getCollectionLocations();
        
        $users = [];
        if ($request->get('filter') !== 'onlyLocations') {
            $users = $company->getFullUserNames();
        }
        
        return new JsonResponse(array_merge($locationCollection, $users), 200);
    }
    
    /**
     * @Route(path="/locationcollectionandusers", name="get_location_collection_and_users", methods={"GET"})
     */
    public function get_location_collection_and_users(CurrentUserProvider $currentUserProvider): Response
    {
        $company = $currentUserProvider->getAuthenticatedUser()->getCompany();
        $locationCollection = $company->getCollectionLocations();
        $users = $company->getFullUserNames();
        
        $result = [
            [
                'name' => 'Users',
                'children' => $users
            ]
        ];
        
        if (count($locationCollection) > 0) {
            $result = array_merge(
                [[
                    'name' => 'Location Collections',
                    'children' => array_values($locationCollection)
                ]],
                $result
            );
        }
        
        return new JsonResponse($result, 200);
    }
    
    /**
     * @Route(path="/locationcollection", name="update_location_collection", methods={"PUT"})
     */
    public function update_location_collection(
        LocationCollectionDto $locationCollectionDto,
        CompanyMapper $companyMapper,
        EntityManagerInterface $entityManager,
        CurrentUserProvider $currentUserProvider,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::ADMIN, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $company = $currentUserProvider->getAuthenticatedUser()->getCompany();
        
        $uniqueCollectionLocationsToBeSaved = array_unique($locationCollectionDto->locationCollection);
        
        $locationCollectionDto->locationCollection =
            array_filter($uniqueCollectionLocationsToBeSaved, function($var) use($company){
                $userNames = $company->getFullUserNames();
                return(!in_array($var, $userNames));
            });
        
        $companyMapper->putLocationCollectionFromDto($locationCollectionDto, $company);
        
        $entityManager->flush();
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="/{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_companies_get")
     */
    public function get(Company $company): Company
    {
        return $company;
    }
    
    /**
     * @Route(path="/own/settings", name="put_settings", methods={"PUT"})
     */
    public function put_settings(
        PutCompanySettings $putCompanySettings,
        CompanyMapper $companyMapper,
        EntityManagerInterface $em,
        Security $security,
        CurrentUserProvider $currentUserProvider
    ) : Response
    {
        $company = $currentUserProvider->getAuthenticatedUser()->getCompany();
        if(!$security->isGranted(Permission::ADMIN, $company)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        try {
            $companyMapper->putCompanySettingsFromDto($putCompanySettings, $company);
            $em->flush();
        } catch (Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="/own", name="put", methods={"PUT"})
     */
    public function put(
        PutCompany $putCompany,
        CompanyMapper $mapper,
        EntityManagerInterface $em,
        Security $security,
        CurrentUserProvider $currentUserProvider
    ) : Response
    {
        $company = $currentUserProvider->getAuthenticatedUser()->getCompany();
        if(!$security->isGranted(Permission::ADMIN, $company)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        try {
            $mapper->putCompanyFromDto($putCompany, $company);
            $em->flush();
        } catch (Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="/own", name="delete", methods={"DELETE"})
     */
    public function delete(
        CompanyService $companyService,
        CurrentUserProvider $currentUserProvider,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response
    {
        $company = $currentUserProvider->getAuthenticatedUser()->getCompany();
        if(!$security->isGranted(Permission::ADMIN, $company)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $companyService->deleteCompany($company);
    
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $company));
    
        $entityManager->flush();
    
        return new Response(null, 204);
    }
}
