<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Exceptions\Domain\InconsistentDataException;
use App\Exceptions\Domain\MissingDataException;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener {
    
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;
    
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }
    
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        
        /** @var User $user */
        $user = $event->getUser();
        $company = $user->getCompany();
        
        if (!$user instanceof UserInterface) {
            return;
        }
        
        if ($user->isDeleted()) {
            throw MissingDataException::forEntityNotFound($user->getId(), User::class);
        }
        
        $source = $this->requestStack->getCurrentRequest()->get('source');
        if (!$source) {
            throw InconsistentDataException::forDataIsMissing('requets source');
        }
        
        $refreshToken = bin2hex(random_bytes(32));
        if ($source === 'webapp') {
            $user->setWebRefreshToken($refreshToken);
        }
        if ($source === 'mobileapp') {
            $user->setMobileRefreshToken($refreshToken);
        }
        $user->setLastLogin(new DateTimeImmutable());
        $this->entityManager->flush();
    
        $expiration = new DateTime('+1 hour');
    
        $data['exp'] = $expiration->getTimestamp();

        $data['refreshtoken'] = $refreshToken;
        
        $data['user'] = array(
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'id' => $user->getId(),
            'company' => [
                'name' => $company->getName(),
                'country' => $company->getCountry()
            ]
        );
        
        $event->setData($data);
    }
    
}
