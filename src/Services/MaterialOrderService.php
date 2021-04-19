<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\Data\MaterialOrderStatus;
use App\Entity\MaterialOrder;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use App\Services\Pdf\MaterialOrder\MaterialOrderPdfService;

class MaterialOrderService
{
    
    private MaterialOrderPdfService $materialOrderPdfService;
    private UglService $uglService;
    private MailerInterface $mailer;
    private CurrentUserProvider $currentUserProvider;
    
    public function __construct(
        MaterialOrderPdfService $materialOrderPdfService,
        UglService $uglService,
        MailerInterface $mailer,
        CurrentUserProvider $currentUserProvider
    )
    {
        $this->materialOrderPdfService = $materialOrderPdfService;
        $this->uglService = $uglService;
        $this->mailer = $mailer;
        $this->currentUserProvider = $currentUserProvider;
    }
    
    public function createMaterialOrderFiles(MaterialOrder $materialOrder): MaterialOrder
    {
        $user = $this->currentUserProvider->getAuthenticatedUser();
        switch ($materialOrder->getMaterialOrderType()) {
            case 'pdf':
                $link = $this->materialOrderPdfService->createSingleMaterialOrder($materialOrder);
                $materialOrder->setFileLink($link);
                $materialOrder->setMaterialOrderStatus(MaterialOrderStatus::complete());
                break;
            case 'email':
                $link = $this->materialOrderPdfService->createSingleMaterialOrder($materialOrder);
                $materialOrder->setFileLink($link);
                $materialOrder->setMaterialOrderStatus(MaterialOrderStatus::complete());
                $salutation = $materialOrder->getSupplier()->getResponsiblePerson();
                if ($materialOrder->getSupplier()->getEmailSalutation()) {
                    $salutation = $materialOrder->getSupplier()->getEmailSalutation();
                }
                $email = (new TemplatedEmail())
                    ->from($user->getEmail())
                    ->to($materialOrder->getSupplier()->getEmail())
                    ->bcc($user->getEmail())
                    ->subject('Neue Bestellung')
                    ->htmlTemplate('emails/businessLogic/materialOrderEmail.html.twig')
                    ->attachFromPath($link)
                    ->context([
                        'salutation' => $salutation,
                        'user' => $user,
                        'company' => $user->getCompany(),
                    ])
                ;
                $this->mailer->send($email);
                break;
            case 'ugl':
                $link = $this->uglService->createUgl($materialOrder);
                $materialOrder->setFileLink($link);
                $materialOrder->setMaterialOrderStatus(MaterialOrderStatus::complete());
                break;
            case 'manual':
                $materialOrder->setMaterialOrderStatus(MaterialOrderStatus::complete());
                break;
        }
        
        return $materialOrder;
    }
}
