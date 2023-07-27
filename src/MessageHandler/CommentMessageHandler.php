<?php

namespace App\MessageHandler;
use AllowDynamicProperties;
use App\Message\Comment\Message;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;

    private MessageBusInterface $bus;

    private WorkflowInterface $workflow;

    private LoggerInterface $logger;
    private MailerInterface $mailer;
    private string $adminEmail;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository,
        MessageBusInterface $bus,
        WorkflowInterface $workflow,
        MailerInterface $mailer,
        string $adminEmail,
        LoggerInterface $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->bus = $bus;
        $this->workflow = $workflow;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->logger = $logger;
    }
    public function __invoke(Message $message): void
    {
        sleep(10);
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment) {
            return;
        }

        if ($this->workflow->can($comment, 'accept')) {
             $score = rand(1,2); //TODO
             $transition = 'accept';

             if (2 === $score) {
                 $transition = 'reject_spam';
             } elseif (1 === $score) {
                 $transition = 'might_be_spam';
             }

             $this->workflow->apply($comment, $transition);
             $this->entityManager->flush();
             $this->entityManager->flush();
             $this->bus->dispatch($message);

        } elseif ($this->workflow->can($comment, 'publish') || $this->workflow->can($comment, 'publish_ham')) {
            $this->mailer->send(new NotificationEmail())
                ->subject('New comment')
                ->htmlTempalte('emails/comment.html.twig')
                ->from($this->adminEmail)
                ->to($this->adminEmail)
                ->context(['comment' => $comment]);

        } elseif ($this->logger) {
             $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }
    }
}