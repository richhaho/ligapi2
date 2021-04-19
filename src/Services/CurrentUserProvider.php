<?php


namespace App\Services;


use App\Entity\Company;
use App\Entity\User;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Security;

class CurrentUserProvider
{
    /**
     * @var Security
     */
    private Security $security;
    private RequestContext $requestContext;
    
    public function __construct(
        Security $security,
        RequestContext $requestContext
    )
    {
        $this->security = $security;
        $this->requestContext = $requestContext;
    }
    
    public function getCompany(): ?Company
    {
        if (!$this->getAuthenticatedUser()) {
            return null;
        }
        return $this->getAuthenticatedUser()->getCompany();
    }
    
    public function getAuthenticatedUser(): ?User
    {
        if ($this->requestContext->getParameter('user')) {
            return $this->requestContext->getParameter('user');
        }
        
        $user = $this->security->getUser();
        
        if (!$user instanceof User) {
//            throw UnsupportedUserException::forUnsupportedUser($user); // TODO: Wieder scharfschalten, gleichzeitig profiler testen
        }
        
        return $user;
    }
}
