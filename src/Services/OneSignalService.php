<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\User;
use App\Repository\UserRepository;

class OneSignalService
{
    
    private CurrentUserProvider $currentUserProvider;
    private string $onesignalAppId;
    private string $onesignalApiToken;
    private UserRepository $userRepository;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        UserRepository $userRepository,
        string $onesignalAppId,
        string $onesignalApiToken
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->onesignalAppId = $onesignalAppId;
        $this->onesignalApiToken = $onesignalApiToken;
        $this->userRepository = $userRepository;
    }
    
    private function sendMessage(array $fields): string
    {
        $fields = json_encode($fields);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $this->onesignalApiToken));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
    
    public function sendForgroundMessage(string $type, string $topic, string $details, array $notificationIds, ?string $url = null): string
    {
        $content = array(
            "en" => $topic
        );
        
        $fields = array(
            'app_id' => $this->onesignalAppId,
            'include_external_user_ids' => $notificationIds,
            'data' => array("type" => $type, "details" => $details),
            'contents' => $content,
            'web_url' => $url
        );
        
        return $this->sendMessage($fields);
    }
    
    public function sendBackgroundMessage(array $content, array $notificationIds): string
    {
        $fields = array(
            'app_id' => $this->onesignalAppId,
            'include_external_user_ids' => $notificationIds,
            'data' => $content,
            "content_available" => true
        );
    
        return $this->sendMessage($fields);
    }
    
    public function informAboutUpdate(object $entity): string
    {
        $currentUser = $this->currentUserProvider->getAuthenticatedUser();
        $allCompanyUsers = $this->userRepository->findAll();
        
        $webIds = [];
    
        /** @var User $user */
        foreach ($allCompanyUsers as $user) {
            if ($user->getId() === $currentUser->getId()) {
                continue;
            }
            if ($user->getWebPushId()) {
                $webIds[] = $user->getWebPushId();
            }
        }
        
        $id = '?';
        
        if (method_exists($entity, 'getId')) {
            $id = $entity->getId();
        }
    
        $content = array(
            "en" => 'entityUpdated',
            "entityType" => get_class($entity),
            "entityId" => $id
        );
        
        return $this->sendBackgroundMessage($content, $webIds);
    }
    
}
