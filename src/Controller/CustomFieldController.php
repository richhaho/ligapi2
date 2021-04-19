<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\CustomFieldDto;
use App\Api\Mapper\CustomFieldMapper;
use App\Entity\Data\Permission;
use App\Entity\CustomField;
use App\Repository\CustomFieldRepository;
use App\Services\CustomFieldService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route(path="/api/customfields/", name="api_custom_field_")
 */
class CustomFieldController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_custom_field_get")
     */
    public function index(CustomFieldRepository $repository): iterable
    {
        return $repository->findBy([], ['name' => 'ASC']);
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_custom_field_get")
     */
    public function get(CustomField $customField): CustomField
    {
        return $customField;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_custom_field_get")
     */
    public function create(
        CustomFieldDto $customFieldDto,
        CustomFieldMapper $mapper,
        EntityManagerInterface $em,
        Security $security
    ): CustomField
    {
        if(!$security->isGranted(Permission::ADMIN)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $customField = $mapper->createCustomFieldFromDto($customFieldDto);
        $em->persist($customField);
        $em->flush();

        return $customField;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     */
    public function put(
        CustomFieldDto $customFieldDto,
        CustomFieldMapper $mapper,
        CustomField $customField,
        EntityManagerInterface $entityManager,
        Security $security
    ): CustomField
    {
        if(!$security->isGranted(Permission::ADMIN)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $customField = $mapper->putCustomFieldFromDto($customFieldDto, $customField);
        $entityManager->flush();
        return $customField;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        CustomField $customField,
        EntityManagerInterface $entityManager,
        Security $security,
        CustomFieldService $customFieldService
    ): Response
    {
        if(!$security->isGranted(Permission::ADMIN)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $customFieldService->removeCustomField($customField);
        $entityManager->remove($customField);
        $entityManager->flush();
        
        return new Response(null, 204);
    }
}
