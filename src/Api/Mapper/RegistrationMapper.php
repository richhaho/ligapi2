<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\RegisterDto;
use App\Entity\Company;
use App\Entity\Consignment;
use App\Entity\User;
use App\Security\UserService;
use App\Services\ItemNumberService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationMapper
{
    use ValidationTrait;
    
    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $encoder;
    private UserService $userService;
    private ValidatorInterface $validator;
    private MailerInterface $mailer;
    private string $frontendUrl;
    private ItemNumberService $itemNumberService;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $encoder,
        UserService $userService,
        ValidatorInterface $validator,
        ItemNumberService $itemNumberService,
        MailerInterface $mailer,
        string $frontendUrl
    )
    {
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
        $this->userService = $userService;
        $this->validator = $validator;
        $this->mailer = $mailer;
        $this->frontendUrl = $frontendUrl;
        $this->itemNumberService = $itemNumberService;
    }
    
    public function registerNewCompany(RegisterDto $registerDto, ?bool $sendConfirmation = true): User
    {
        $this->validate($registerDto);
        $this->validate($registerDto->company);
        
        $company = new Company($registerDto->company->name, $registerDto->company->termsAccepted);
        $this->entityManager->persist($company);
        
        $user = $this->userService->createUser($registerDto->firstName, $registerDto->lastName, $registerDto->email, $registerDto->password, $company);
        $user->setIsAdmin(true);
        $webRefreshToken = bin2hex(random_bytes(32));
        $user->setWebRefreshToken($webRefreshToken);
        $user->setMobileRefreshToken($webRefreshToken);
        $consignmentNumber = $this->itemNumberService->getNextItemNumber(Consignment::class, $company);
        $userConsignment = new Consignment($company, $consignmentNumber, null, $user);
        $this->entityManager->persist($userConsignment);
    
        if ($sendConfirmation) {
            $email = (new TemplatedEmail())
                ->from('kontakt@lagerimgriff.de')
                ->to($user->getEmail())
                ->subject('Lager im Griff Registrierung bestätigen')
                ->htmlTemplate('emails/auth/confirmRegistration.html.twig')
                ->context([
                    'user' => $user,
                    'company' => $user->getCompany(),
                    'confirmUrl' => $this->frontendUrl . '/opt-in-confirmation?token=' . $user->getWebRefreshToken()
                    
                ])
            ;
            $this->mailer->send($email);
        }
        
        return $user;
    }
}
