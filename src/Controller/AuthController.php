<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\Dto\CompanyDto;
use App\Api\Dto\ForgotPasswordDto;
use App\Api\Dto\RefreshTokenDto;
use App\Api\Dto\RegisterDto;
use App\Api\Dto\IdDto;
use App\Api\Dto\ResetPasswordDto;
use App\Api\Mapper\RegistrationMapper;
use App\Entity\User;
use App\Exceptions\Domain\InconsistentDataException;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\UserRepository;
use App\Services\CleverReachService;
use App\Services\UserService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/auth", name="api_auth_")
 */
class AuthController
{
    /**
     * @Route(path="/check", name="check", methods={"GET"})
     */
    public function get(): Response
    {
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="/register", name="register", methods={"POST"})
     */
    public function register(
        RegisterDto $registerDto,
        RegistrationMapper $registrationMapper,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $registrationMapper->registerNewCompany($registerDto);
        $entityManager->persist($user);
        $entityManager->flush();
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="/refresh", name="soft_login", methods={"POST"})
     */
    public function soft_login(
        RefreshTokenDto $refreshTokenDto,
        UserRepository $userRepository,
        JWTTokenManagerInterface $jwtTokenManager,
        EventDispatcherInterface $dispatcher,
        Request $request
    ): Response
    {
        $source = $request->get('source');
        if (!$source) {
            throw InconsistentDataException::forDataIsMissing('request source');
        }
        /** @var User $user */
        $user = $userRepository->findByRefreshToken($refreshTokenDto->refreshToken, $source);
        if (!$user) {
            return new Response(null, 401);
        }
        
        $jwt = $jwtTokenManager->create($user);
        
        $response = new JWTAuthenticationSuccessResponse($jwt);
        $event    = new AuthenticationSuccessEvent(['token' => $jwt], $user, $response);
        
        $dispatcher->dispatch($event, Events::AUTHENTICATION_SUCCESS);
        
        $responseData = $event->getData();
        
        if ($responseData) {
            $response->setData($responseData);
        } else {
            $response->setStatusCode(JWTAuthenticationSuccessResponse::HTTP_NO_CONTENT);
        }
        
        return $response;
    }
    
    /**
     * @Route(path="/confirm/{token}", name="confirm_doubleoptin", methods={"GET"})
     */
    public function confirm_doubleoptin(
        string $token,
        UserRepository $userRepository,
        JWTTokenManagerInterface $jwtTokenManager,
        EventDispatcherInterface $dispatcher,
        CleverReachService $cleverReachService
    ): Response
    {
        /** @var User $user */
        $user = $userRepository->findByRefreshToken($token, 'webapp');
        if (!$user) {
            return new Response(null, 401);
        }
    
        $user->setDoubleOptIn(true);
        
        $jwt = $jwtTokenManager->create($user);
        
        $response = new JWTAuthenticationSuccessResponse($jwt);
        $event    = new AuthenticationSuccessEvent(['token' => $jwt], $user, $response);
        
        $dispatcher->dispatch($event, Events::AUTHENTICATION_SUCCESS);
        
        $responseData = $event->getData();
        
        if ($responseData) {
            $response->setData($responseData);
        } else {
            $response->setStatusCode(JWTAuthenticationSuccessResponse::HTTP_NO_CONTENT);
        }
    
        $cleverReachService->addEmail($user->getEmail());
        
        return $response;
    }
    
    /**
     * @Route(path="/logout", name="logout", methods={"POST"})
     */
    public function logout(
        RefreshTokenDto $refreshTokenDto,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        Request $request
    ): Response
    {
        $source = $request->get('source');
        if (!$refreshTokenDto->refreshToken || !$source) {
            return new Response(null, 204);
        }
        $user = $userRepository->findByRefreshToken($refreshTokenDto->refreshToken, $source);
        if ($user) {
            if ($source === 'mobileapp') {
                $user->setMobileRefreshToken(null);
            }
            if ($source === 'webapp') {
                $user->setWebRefreshToken(null);
            }
            $entityManager->flush();
        }
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="/uuidlogin", name="uuidlogin", methods={"POST"})
     */
    public function uuidlogin(
        IdDto $uuidLoginDto,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtTokenManager,
        RegistrationMapper $registrationMapper,
        EventDispatcherInterface $dispatcher
    ): Response
    {
        $deviceUuid = $uuidLoginDto->uuid;
        if (!$deviceUuid) {
            throw MissingDataException::forMissingData('uuid');
        }
        $user = $userRepository->findByUuid($deviceUuid);
        
        $firstLogin = false;
        
        if (!$user) {
            $registerDto = new RegisterDto();
            $registerDto->firstName = 'Vorname';
            $registerDto->lastName = 'Nachname';
            $registerDto->password = $deviceUuid;
            $registerDto->email = $deviceUuid . '@lagerimgriff.de';
            $company = new CompanyDto();
            $company->name = $deviceUuid;
            $company->termsAccepted = true;
            $registerDto->company = $company;
            
            $user = $registrationMapper->registerNewCompany($registerDto, false);
            $user->setDeviceUuid($deviceUuid);
            $entityManager->persist($user);
            $entityManager->flush();
            $firstLogin = true;
        }
    
        $jwt = $jwtTokenManager->create($user);
    
        $response = new JWTAuthenticationSuccessResponse($jwt);
        $event    = new AuthenticationSuccessEvent(['token' => $jwt], $user, $response);
    
        $dispatcher->dispatch($event, Events::AUTHENTICATION_SUCCESS);
    
        $responseData = $event->getData();
    
        $data['exp'] = (new DateTime('+1 hour'))->getTimestamp();
    
        $data['refreshtoken'] = $user->getMobileRefreshToken();
    
        $responseData['user'] = array(
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'id' => $user->getId(),
            'company' => [
                'name' => $user->getCompany()->getName(),
                'country' => $user->getCompany()->getCountry()
            ],
            'firstLogin' => $firstLogin
        );
        $response->setData($responseData);
    
        return $response;
    }
    
    /**
     * @Route(path="/forgotpassword", name="forgotpassword", methods={"POST"})
     */
    public function forgotpassword(
        ForgotPasswordDto $resetPasswordDto,
        UserRepository $userRepository,
        UserService  $userService,
        EntityManagerInterface $entityManager
    ): Response
    {
        $companyFilterWasEnabled = false;
        if ($entityManager->getFilters()->isEnabled('company')) {
            $companyFilterWasEnabled = true;
            $entityManager->getFilters()->disable('company');
        }
        
        $email = $resetPasswordDto->email;
        
        $user = $userRepository->getByEmail($email);
        
        if (!$user) {
            throw MissingDataException::forEntityNotFound($email, User::class);
        }
    
        
        $userService->sendResetToken($user);
    
        if ($companyFilterWasEnabled) {
            $entityManager->getFilters()->enable('company');
        }
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="/resetpassword", name="resetpassword", methods={"POST"})
     */
    public function resetpassword(
        ResetPasswordDto $resetPasswordDto,
        UserRepository $userRepository,
        UserService $userService,
        JWTTokenManagerInterface $jwtTokenManager,
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager
    ): Response
    {
        $companyFilterWasEnabled = false;
        if ($entityManager->getFilters()->isEnabled('company')) {
            $companyFilterWasEnabled = true;
            $entityManager->getFilters()->disable('company');
        }
        
        $user = $userRepository->getByResetPasswordToken($resetPasswordDto->token);
        
        if (!$user) {
            throw MissingDataException::forEntityNotFound($resetPasswordDto->token, User::class);
        }
        
        $userService->updatePassword($user, $resetPasswordDto->newPassword);
        
        if (!$user) {
            return new Response(null, 401);
        }
        
        $jwt = $jwtTokenManager->create($user);
        
        $response = new JWTAuthenticationSuccessResponse($jwt);
        $event    = new AuthenticationSuccessEvent(['token' => $jwt], $user, $response);
        
        $dispatcher->dispatch($event, Events::AUTHENTICATION_SUCCESS);
        
        $responseData = $event->getData();
        
        if ($responseData) {
            $response->setData($responseData);
        } else {
            $response->setStatusCode(JWTAuthenticationSuccessResponse::HTTP_NO_CONTENT);
        }
    
        if ($companyFilterWasEnabled) {
            $entityManager->getFilters()->enable('company');
        }
        
        return $response;
    }
}
