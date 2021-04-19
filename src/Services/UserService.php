<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\Consignment;
use App\Entity\Data\ChangeAction;
use App\Entity\StockChange;
use App\Entity\Task;
use App\Entity\User;
use App\Event\ChangeEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserService
{
    
    private EntityManagerInterface $entityManager;
    private CurrentUserProvider $currentUserProvider;
    private EventDispatcherInterface $eventDispatcher;
    private string $frontendUrl;
    private UserPasswordEncoderInterface $encoder;
    private MailerInterface $mailer;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        CurrentUserProvider $currentUserProvider,
        EventDispatcherInterface $eventDispatcher,
        UserPasswordEncoderInterface $encoder,
        MailerInterface $mailer,
        string $frontendUrl
    )
    {
        $this->entityManager = $entityManager;
        $this->currentUserProvider = $currentUserProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->frontendUrl = $frontendUrl;
        $this->encoder = $encoder;
        $this->mailer = $mailer;
    }
    
    public function removeRelatedEntitiesFromUser(User $user): void
    {
        $currentUser = $this->currentUserProvider->getAuthenticatedUser();
        
        foreach($user->getConsignments() as $consignment) {
            $this->entityManager->remove($consignment);
        }
    
        /** @var Task $task */
        foreach ($user->getTasks() as $task) {
            $task->setResponsible($currentUser);
            $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $task));
        }
    
        if ($user->getLocation()) {
            $user->getLocation()->resetUser();
            $user->getLocation()->setName($user->getFullName());
        }
    
        /** @var StockChange $stockChange */
        foreach ($user->getStockChanges() as $stockChange) {
            $stockChange->setUser($currentUser);
            $stockChange->setNote('Ursprünglicher Nutzer: ' . $user->getFullName() . ' ' . $stockChange->getNote());
        }
    
        /** @var Consignment $consignment */
        foreach ($user->getConsignments() as $consignment) {
            $consignmentItems = $consignment->getConsignmentItems();
            foreach ($consignmentItems as $consignmentItem) {
                $this->entityManager->remove($consignmentItem);
            }
            $this->entityManager->remove($consignment);
        }
    }
    
    public function sendResetToken(User $user): void
    {
        $token = md5(random_bytes(20));
        
        $expiration = time() + (24 * 60 * 60);
        
        $user->setPasswordResetToken($token);
        $user->setPasswordResetExpires($expiration);
        
        $this->entityManager->flush();
        
        $this->sendForgotMail($user);
    }
    
    private function sendForgotMail (User $user)
    {
        $resetLink = $this->frontendUrl . '/reset-password?token=' . $user->getPasswordResetToken();
        $salutation = 'Guten Tag ' . $user->getFullName();
        $email = (new TemplatedEmail())
            ->from('kontakt@lagerimgriff.de')
            ->to($user->getEmail())
            ->subject('Passwort zurücksetzen')
            ->htmlTemplate('emails/auth/forgotPassword.html.twig')
            ->context([
                'salutation' => $salutation,
                'companyName' => $user->getCompany()->getName(),
                'resetLink' => $resetLink,
            ])
        ;
        
        if (isset($email)) {
            $this->mailer->send($email);
        }
    }
    
    public function updatePassword(User $user, string $newPassword)
    {
        $user->updatePassword($newPassword, $this->encoder);
        $user->setPasswordResetToken(null);
        $user->setPasswordResetExpires(null);
        
        $this->entityManager->flush();
        
        $salutation = 'Guten Tag ' . $user->getFullName();
        $email = (new TemplatedEmail())
            ->from('kontakt@lagerimgriff.de')
            ->to($user->getEmail())
            ->subject('Passwort zurückgesetzt')
            ->htmlTemplate('emails/auth/resetPassword.html.twig')
            ->context([
                'salutation' => $salutation,
                'companyName' => $user->getCompany()->getName()
            ])
        ;
        
        if (isset($email)) {
            $this->mailer->send($email);
        }
    }
}
