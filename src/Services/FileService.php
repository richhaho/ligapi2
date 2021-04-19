<?php

declare(strict_types=1);


namespace App\Services;


use App\Api\Dto\FileDto;
use App\Entity\Company;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\File;
use App\Entity\FileAwareInterface;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Exceptions\Domain\MissingDataException;
use Doctrine\ORM\EntityManagerInterface;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FileService
{
    private FilesystemInterface $defaultStorage;
    private EntityManagerInterface $entityManager;
    
    const ALLOWED_MIMETYPES = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    const IMAGE_MIMETYPES = ['image/jpeg', 'image/png', 'image/gif'];
    private MimeTypesInterface $mimeTypes;
    private ImagineInterface $imagine;
    private EventDispatcherInterface $eventDispatcher;
    
    /**
     * FileService constructor.
     */
    public function __construct(
        FilesystemInterface $defaultStorage,
        EntityManagerInterface $entityManager,
        MimeTypesInterface $mimeTypes,
        ImagineInterface $imagine,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->defaultStorage = $defaultStorage;
        $this->entityManager = $entityManager;
        $this->mimeTypes = $mimeTypes;
        $this->imagine = $imagine;
        $this->eventDispatcher = $eventDispatcher;
    }
    
    private function saveFileFromUpload(\Symfony\Component\HttpFoundation\File\File $uploadedFile, string $path): void
    {
        $stream = fopen($uploadedFile->getRealPath(), 'r+');
        $this->defaultStorage->writeStream($path, $stream);
        fclose($stream);
    }
    
    public function getNewFilePath(string $mimeType, Company $company): string
    {
        $extention = $this->mimeTypes->getExtensions($mimeType)[0];
        $relativePath = Uuid::uuid4()->toString() . '.' . $extention;
        return 'companyData/' . $company->getId() . '/uploads/' . $relativePath;
    }
    
    private function removeOldProfileImage(FileAwareInterface $entity)
    {
        foreach ($entity->getAllFiles() as $index => $fileArray) {
            $file = File::fromArray($fileArray);
            if ($file->getDocType() === 'profileImage' || $file->getDocType() === 'thumb') {
                $this->defaultStorage->delete($file->getRelativePath());
                $entity->removeFile($file);
            }
        }
    }
    
    public function addFileToEntity(
        \Symfony\Component\HttpFoundation\File\File $uploadedFile,
        FileAwareInterface $fileAwareEntity,
        string $docType,
        string $originalFullName,
        ?string $originalPath = null
    )
    {
        $mimeType = $uploadedFile->getMimeType();
        
        $originalExtention = $originalExtention = $uploadedFile->getExtension();
        $originalName = str_replace($originalExtention, '', $originalFullName);
        
        if (!in_array($mimeType, self::ALLOWED_MIMETYPES)) {
            throw InvalidArgumentException::forUnsupportedMimeType($originalName, $mimeType, implode('/', self::ALLOWED_MIMETYPES));
        }
        
        if ($docType === 'profileImage') {
            $this->removeOldProfileImage($fileAwareEntity);
        }
        
        $width = null;
        $height = null;
        
        if (in_array($mimeType, self::IMAGE_MIMETYPES)) {
            $image = $this->imagine->open($uploadedFile->getRealPath());
            $path = $this->getNewFilePath('image/jpeg', $fileAwareEntity->getCompany());
            $this->defaultStorage->write($path, $image->thumbnail(new Box(1500, 1500))->get('jpg', ['jpeg_quality' => 80]));
            $width = $image->getSize()->getWidth();
            $height = $image->getSize()->getHeight();
            
            if ($docType === 'profileImage') {
                $this->removeOldProfileImage($fileAwareEntity);
                $pathThumb = $this->getNewFilePath('image/jpeg', $fileAwareEntity->getCompany());
                $this->defaultStorage->write($pathThumb, $image->thumbnail(new Box(200, 200))->get('jpg', ['jpeg_quality' => 80]));
                $width = $image->getSize()->getWidth();
                $height = $image->getSize()->getHeight();
                $fileEntity = new File(
                    $pathThumb,
                    $originalName,
                    $mimeType,
                    0,
                    'thumb'
                );

                $fileAwareEntity->addFile($fileEntity);
            }
        } else {
            $path = $this->getNewFilePath($mimeType, $fileAwareEntity->getCompany());
            $this->saveFileFromUpload($uploadedFile, $path);
        }
        
        $fileEntity = new File(
            $path,
            $originalName,
            $mimeType,
            $uploadedFile->getSize(),
            $docType,
            $width,
            $height,
            $originalPath
        );
    
        $fileAwareEntity->addFile($fileEntity);
    }
    
    public function removeFile(string $entityClass, string $id, FileDto $fileDto): void
    {
        $entity = $this->entityManager->getRepository($entityClass)->find($id);
    
        if (!$entity) {
            throw MissingDataException::forEntityNotFound($id, $entityClass);
        }
    
        if (!$entity instanceof FileAwareInterface) {
            throw InvalidArgumentException::forEntityDoesNotSupportFiles($id, $entityClass);
        }
    
        $file = File::fromDto($fileDto);
    
        if ($file->getDocType() === 'profileImage') {
            $this->defaultStorage->delete($entity->getThumbFile()->getRelativePath());
            $entity->removeFile($entity->getThumbFile());
        }
        $this->defaultStorage->delete($file->getRelativePath());
        $entity->removeFile($file);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $entity));
    }
}
