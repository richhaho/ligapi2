<?php

declare(strict_types=1);


namespace App\Services;


use App\Api\Dto\DirectOrderDto;
use App\Entity\Data\AutoStatus;
use App\Entity\DirectOrder;
use App\Entity\DirectOrderPosition;
use App\Entity\DirectOrderPositionResult;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\DirectOrderPositionResultRepository;
use App\Repository\OrderSourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class DirectOrderService
{
    private DirectOrderPositionResultRepository $directOrderPositionResultRepository;
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;
    private OneSignalService $oneSignalService;
    private OrderSourceRepository $orderSourceRepository;
    private string $frontendUrl;
    
    public function __construct(
        DirectOrderPositionResultRepository $directOrderPositionResultRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        OneSignalService $oneSignalService,
        OrderSourceRepository $orderSourceRepository,
        string $frontendUrl
    )
    {
        $this->directOrderPositionResultRepository = $directOrderPositionResultRepository;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->oneSignalService = $oneSignalService;
        $this->orderSourceRepository = $orderSourceRepository;
        $this->frontendUrl = $frontendUrl;
    }
    
    public function getNextBatchOfNewDirectOrderPositionResults(int $limit = null)
    {
        /** @var DirectOrderPositionResult $firstDirectOrderPositionResult */
        $firstNewDirectOrderPositionResult = $this->directOrderPositionResultRepository->getOldestNewDirectOrderPositionResult();
        if (!$firstNewDirectOrderPositionResult) {
            return 0;
        }
        
        /** @var DirectOrderPositionResult[] $directOrderPositionResultsToProcess */
        $directOrderPositionResultsToProcess = $this->directOrderPositionResultRepository
            ->getDirectOrderPositionResultsToProcess($firstNewDirectOrderPositionResult->getOrderSource()->getSupplier(), $limit);
        
        return $directOrderPositionResultsToProcess;
    }
    
    public function setStatusOfDirectOrderPositionResults(array $directOrderPositionResultIds, AutoStatus $autoStatus)
    {
        foreach ($directOrderPositionResultIds as $directOrderPositionResultId) {
            $directOrderPositionResult = $this->directOrderPositionResultRepository->find($directOrderPositionResultId);
            if (!$directOrderPositionResult) {
                throw MissingDataException::forEntityNotFound($directOrderPositionResult, DirectOrderPositionResult::class);
            }
            $directOrderPositionResult->setAutoStatus($autoStatus);
        }
        $this->entityManager->flush();
    }
    
    public function checkDirectOrderStatusAndSendConfirmation(DirectOrder $directOrder): void
    {
        /** @var DirectOrderPosition $directOrderPosition */
        foreach ($directOrder->getDirectOrderPositions() as $directOrderPosition) {
            /** @var DirectOrderPositionResult $directOrderPositionResult */
            foreach ($directOrderPosition->getDirectOrderPositionResults() as $directOrderPositionResult) {
                if ($directOrderPositionResult->getAutoStatus() !== AutoStatus::complete()->getValue()) {
                    return;
                }
            }
        }
        
        $directOrder->setStatus(AutoStatus::complete());
        $this->entityManager->flush();
        
        $user = $directOrder->getUser();
        
        $webDirectOrderLink = $this->frontendUrl . '/orders/direct/details?directOrderId=' . $directOrder->getId();
    
        $email = (new TemplatedEmail())
            ->from('kontakt@lagerimgriff.de')
            ->to($user->getEmail())
            ->bcc('direktbestellung@steffengrell.de')
            ->subject('Direktbestellung bearbeitet')
            ->htmlTemplate('emails/businessLogic/directOrderEmail.html.twig')
            ->context([
                'salutation' => 'Guten Tag ' . $user->getFirstName() . ' ' . $user->getLastName(),
                'directOrderLink' => $webDirectOrderLink,
                'directOrderInfos' => 'Hauptlieferant: ' . $directOrder->getMainSupplier()->getName(),
            ])
        ;
        $this->mailer->send($email);
    
        $webPushId = $user->getWebPushId();
        
        if ($webPushId) {
            $this->oneSignalService->sendForgroundMessage(
                'directOrder',
                'Direktbestellung bereit',
                'Direktbestellung kann weiter verarbeitet werden',
                [$webPushId],
                $webDirectOrderLink
            );
        }
    }
    
    private function createDirectOrderPositionResults(DirectOrderPosition $directOrderPosition, Supplier $mainSupplier): void
    {
        $mainOrderSource = $this->orderSourceRepository->getOrdersourceOfSupplierAndOrderNumber($mainSupplier, $directOrderPosition->getOrderNumber());
        
        if (!$mainOrderSource) {
            throw MissingDataException::forDirectOrderOrderSourceMissing($directOrderPosition->getOrderNumber(), $mainSupplier);
        }
        
        $material = $mainOrderSource->getMaterial();
        
        $allOrderSources = $material->getOrderSources();
    
        /** @var OrderSource $orderSource */
        foreach ($allOrderSources as $orderSource) {
            $connectedSupplier = $orderSource->getSupplier()->getConnectedSupplier();
            if (!$connectedSupplier) {
                continue;
            }
            $directOrderPositionResult = new DirectOrderPositionResult($orderSource, $directOrderPosition);
            $this->entityManager->persist($directOrderPositionResult);
        }
    }
    
    public function createDirectOrderPositionsAndResultsOfDirectOrderDto(DirectOrderDto $directOrderDto, DirectOrder $directOrder, Supplier $mainSupplier): void
    {
        foreach ($directOrderDto->directOrderPositions as $directOrderPositionDto) {
            $directOrderPosition = new DirectOrderPosition($directOrder, $directOrderPositionDto->orderNumber, $directOrderPositionDto->amount);
            $this->entityManager->persist($directOrderPosition);
    
            $this->createDirectOrderPositionResults($directOrderPosition, $mainSupplier);
        }
    }
}
