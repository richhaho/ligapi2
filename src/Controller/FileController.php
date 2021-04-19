<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\Dto\CreateMaterialDto;
use App\Api\Dto\FileDto;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\File;
use App\Entity\FileAwareInterface;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Exceptions\Domain\MissingDataException;
use App\Form\SingleFileUploadType;
use App\Services\FileService;
use App\Services\Import\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/files/", name="api_file_")
 */
class FileController
{
    /**
     * @Route(path="company/{id}", name="create_company", methods={"POST"}, defaults={"entityClass": "App\Entity\Company"})
     * @Route(path="material/{id}", name="create_material", methods={"POST"}, defaults={"entityClass": "App\Entity\Material"})
     * @Route(path="tool/{id}", name="create_tool", methods={"POST"}, defaults={"entityClass": "App\Entity\Tool"})
     * @Route(path="keyy/{id}", name="create_keyy", methods={"POST"}, defaults={"entityClass": "App\Entity\Keyy"})
     * @Route(path="task/{id}", name="create_task", methods={"POST"}, defaults={"entityClass": "App\Entity\Task"})
     * @Route(path="stockChange/{id}", name="create_stock_change", methods={"POST"}, defaults={"entityClass": "App\Entity\StockChange"})
     */
    public function create(
        string $entityClass,
        string $id,
        Request $request,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        FileService $fileService,
        EventDispatcherInterface $eventDispatcher
    ): Response
    {
        $entity = $entityManager->getRepository($entityClass)->find($id);
        
        if (!$entity) {
            throw MissingDataException::forEntityNotFound($id, $entityClass);
        }
        
        if (!$entity instanceof FileAwareInterface) {
            throw InvalidArgumentException::forEntityDoesNotSupportFiles($id, $entityClass);
        }
        
        $form = $formFactory->create(SingleFileUploadType::class);
        $form->handleRequest($request);
    
        /** @var UploadedFile $file */
        $file = $form->getData()['file'];
    
        if (!$file->isValid()) {
            throw InvalidArgumentException::forInvalidFile($file->getClientOriginalName());
        }
        
        $fileService->addFileToEntity($file, $entity, $form['docType']->getData(), $file->getClientOriginalName());
    
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
    
        $entityManager->flush();
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="material/{id}", name="update_material", methods={"PUT"}, defaults={"entityClass": "App\Entity\Material"})
     * @Route(path="tool/{id}", name="update_tool", methods={"PUT"}, defaults={"entityClass": "App\Entity\Tool"})
     * @Route(path="keyy/{id}", name="update_keyy", methods={"PUT"}, defaults={"entityClass": "App\Entity\Keyy"})
     * @Route(path="task/{id}", name="update_task", methods={"PUT"}, defaults={"entityClass": "App\Entity\Task"})
     */
    public function put(
        string $entityClass,
        string $id,
        FileDto $fileDto,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ): Response
    {
        $entity = $entityManager->getRepository($entityClass)->find($id);
    
        if (!$entity) {
            throw MissingDataException::forEntityNotFound($id, $entityClass);
        }
    
        if (!$entity instanceof FileAwareInterface) {
            throw InvalidArgumentException::forEntityDoesNotSupportFiles($id, $entityClass);
        }
        
        $file = File::fromDto($fileDto);
    
        $entity->updateFile($file);
    
        $entityManager->flush();
    
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
    
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="material/{id}/delete", name="delete_material", methods={"POST"}, defaults={"entityClass": "App\Entity\Material"})
     * @Route(path="tool/{id}/delete", name="delete_tool", methods={"POST"}, defaults={"entityClass": "App\Entity\Tool"})
     * @Route(path="keyy/{id}/delete", name="delete_keyy", methods={"POST"}, defaults={"entityClass": "App\Entity\Keyy"})
     * @Route(path="task/{id}/delete", name="delete_task", methods={"POST"}, defaults={"entityClass": "App\Entity\Task"})
     */
    public function delete(
        string $entityClass,
        string $id,
        FileDto $fileDto,
        FileService $fileService,
        EntityManagerInterface $entityManager
    ): Response
    {
        $fileService->removeFile($entityClass, $id, $fileDto);
    
        $entityManager->flush();
    
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="import/", name="import", methods={"POST"})
     */
    public function import(
        Request $request,
        FormFactoryInterface $formFactory,
        ImportService $importService
    ): Response
    {
        $form = $formFactory->create(SingleFileUploadType::class);
        $form->handleRequest($request);
        
        /** @var UploadedFile $file */
        $file = $form->getData()['file'];
        
        if (!$file->isValid()) {
            throw InvalidArgumentException::forInvalidFile($file->getClientOriginalName());
        }
        
        $importService->importFile($file);
    
        return new Response(null, 204);
    }
}
