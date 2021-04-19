<?php

declare(strict_types=1);


namespace App\EventListener;


use App\Services\BatchChanges\Messages\CreateMany;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Mime\Email;

class MessageFailedListener implements EventSubscriberInterface
{
    
    private MailerInterface $mailer;
    
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed'
        ];
    }
    
    public function onMessageFailed(WorkerMessageFailedEvent $event)
    {
        $message = $event->getEnvelope()->getMessage();
        
        $subject = 'Message failed: ' . get_class($message) . ' | ' . $event->getReceiverName();
        $content = '<h1>Message failed with content:</h1>';
        
        if (get_class($message) === CreateMany::class) {
            $content .= sprintf('<p>UserId: %s</p>', $message->getUserId());
            $content .= sprintf('<p><strong>Error:</strong><br>%s</p>', $event->getThrowable()->getMessage());
            $content .= sprintf('<p><strong>Trace:</strong> <br>%s</p>', json_encode($event->getThrowable()->getTrace()));
            $content .= sprintf('<h4>Dtos:</h4>');
            foreach ($message->getDtos() as $dto) {
                $content .= sprintf('<pre><code>%s</pre></code>', json_encode($dto));
            }
        } else {
            $content = json_encode($message);
        }
        
        $email = (new Email())
            ->from('kontakt@lagerimgriff.de')
            ->to('kontakt@steffengrell.de')
            ->subject($subject)
            ->html($content)
        ;
        
        $this->mailer->send($email);
    }
}
