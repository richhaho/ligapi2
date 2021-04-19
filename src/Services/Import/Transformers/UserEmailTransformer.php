<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\IdDto;
use App\Entity\User;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\UserRepository;
use App\Services\CurrentUserProvider;

class UserEmailTransformer implements TransformerInterface
{
    private UserRepository $userRepository;
    private CurrentUserProvider $currentUserProvider;
    
    public function __construct(
        UserRepository $userRepository,
        CurrentUserProvider $currentUserProvider
    )
    {
        $this->userRepository = $userRepository;
        $this->currentUserProvider = $currentUserProvider;
    }
    
    public function supports(string $transformer): bool
    {
        return $transformer === UserEmailTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): IdDto
    {
        $user = null;
        
        $userEmail = $data[$title];
        
        $idDto = new IdDto();
        
        if (!$userEmail) {
            $user = $this->currentUserProvider->getAuthenticatedUser();
            $idDto->id = $user->getId();
            return $idDto;
        }
        
        $user = $this->userRepository->getByEmail($userEmail);
        
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userEmail, User::class, 'email');
        }
        
        $idDto->id = $user->getId();
        
        return $idDto;
    }
}
