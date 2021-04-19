<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\FileDto;
use App\Entity\Data\File;
use App\Entity\FileAwareInterface;
use App\Entity\User;
use App\Exceptions\Domain\MissingDataException;
use App\Exceptions\Domain\UnsupportedMethodException;
use App\Repository\KeyyRepository;
use App\Repository\MaterialRepository;
use App\Repository\ToolRepository;
use App\Repository\UserRepository;
use App\Services\Crawler\Downloader;
use App\Services\FileService;
use Symfony\Component\Routing\RequestContext;
use Throwable;

class FileMapper implements MapperInterface
{
    
    
    private MaterialRepository $materialRepository;
    private ToolRepository $toolRepository;
    private KeyyRepository $keyyRepository;
    private UserRepository $userRepository;
    private RequestContext $requestContext;
    private FileService $fileService;
    private Downloader $downloader;
    
    public function __construct(
        MaterialRepository $materialRepository,
        ToolRepository $toolRepository,
        KeyyRepository $keyyRepository,
        UserRepository $userRepository,
        RequestContext $requestContext,
        FileService $fileService,
        Downloader $downloader
    )
    {
        $this->materialRepository = $materialRepository;
        $this->toolRepository = $toolRepository;
        $this->keyyRepository = $keyyRepository;
        $this->userRepository = $userRepository;
        $this->requestContext = $requestContext;
        $this->fileService = $fileService;
        $this->downloader = $downloader;
    }
    
    public function supports(string $dtoName): bool
    {
        return $dtoName === FileDto::class;
    }
    
    /**
     * @param FileDto $dto
     */
    public function createEntityFromDto(DtoInterface $dto, string $userId)
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $entity = null;
    
        if ($dto->material) {
            if ($dto->material->originalId) {
                $entity = $this->materialRepository->findByAltScannerId($dto->material->originalId);
            } else {
                $entity = $this->materialRepository->find($dto->material->id);
            }
        }
        if ($dto->tool) {
            if ($dto->tool->originalId) {
                $entity = $this->toolRepository->findByAltScannerId($dto->tool->originalId);
            } else {
                $entity = $this->toolRepository->find($dto->tool->id);
            }
        }
        if ($dto->keyy) {
            if ($dto->keyy->originalId) {
                $entity = $this->keyyRepository->findByAltScannerId($dto->keyy->originalId);
            } else {
                $entity = $this->keyyRepository->find($dto->keyy->id);
            }
        }
        
        if (!$entity) {
            throw MissingDataException::forMissingData('Linked entity');
        }
        $fileName = $dto->displayedName ?? $dto->docType;
        
        try {
            $this->fileService->addFileToEntity($this->downloader->downloadCompanyUrl($dto->originalPath), $entity, $dto->docType, $fileName, $dto->originalPath);
        } catch (Throwable $e) {
            // ToDo: Log
        }
    
        return $entity;
    }
    
    public function putEntityFromDto(DtoInterface $dto, object $entity)
    {
        throw UnsupportedMethodException::forUnsupportedMethod('putEntityFromDto');
    }
    
    public function patchEntityFromDto(DtoInterface $dto, object $entity)
    {
        throw UnsupportedMethodException::forUnsupportedMethod('patchEntityFromDto');
    }
    
    /**
     * @param FileDto $dto
     */
    public function prepareImport(DtoInterface $dto, string $userId): ?FileDto
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
    
        /** @var FileAwareInterface $entity */
        $entity = null;
        
        if ($dto->material) {
            if ($dto->material->originalId) {
                $entity = $this->materialRepository->findByAltScannerId($dto->material->originalId);
            } else {
                $entity = $this->materialRepository->find($dto->material->id);
            }
        }
        if ($dto->tool) {
            if ($dto->tool->originalId) {
                $entity = $this->toolRepository->findByAltScannerId($dto->tool->originalId);
            } else {
                $entity = $this->toolRepository->find($dto->tool->id);
            }
        }
        if ($dto->keyy) {
            if ($dto->keyy->originalId) {
                $entity = $this->keyyRepository->findByAltScannerId($dto->keyy->originalId);
            } else {
                $entity = $this->keyyRepository->find($dto->keyy->id);
            }
        }
    
        if (!$entity) {
            return null;
        }
    
        foreach ($entity->getAllFiles() as $file) {
            if (File::fromArray($file)->getOriginalPath() === $dto->originalPath) {
                return null;
            }
        }
        
        return $dto;
    }
}
