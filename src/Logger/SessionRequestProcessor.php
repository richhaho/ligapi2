<?php

declare(strict_types=1);


namespace App\Logger;


use App\Services\CurrentUserProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Throwable;

class SessionRequestProcessor
{
    private CurrentUserProvider $currentUserProvider;
    private RequestStack $requestStack;
    private RequestContext $requestContext;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        RequestStack $requestStack,
        RequestContext $requestContext
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->requestStack = $requestStack;
        $this->requestContext = $requestContext;
    }
    
    // this method is called for each log record; optimize it to not hurt performance
    public function __invoke(array $record): array
    {
        $user = $this->currentUserProvider->getAuthenticatedUser();
    
        try {
            $url = $this->requestStack->getMasterRequest()->getRequestUri();
            $method = $this->requestStack->getMasterRequest()->getMethod();
            $all = $this->requestStack->getMasterRequest()->getContent();
    
            $record['extra']['url'] = $url;
            $record['extra']['method'] = $method;
            $record['extra']['all'] = json_encode($all);
        } catch (Throwable $e) {
            $record['extra']['url'] = '';
            $record['extra']['method'] = '';
            $record['extra']['all'] = '';
        }
        
        if (!$user) {
            $record['extra']['userId'] = 'NO USER';
            $record['extra']['companyId'] = 'NO COMPANY';
            return $record;
        }
        
        $record['extra']['userId'] = $user->getId();
        $record['extra']['companyId'] = $user->getCompany()->getId();
        
        return $record;
    }
}
