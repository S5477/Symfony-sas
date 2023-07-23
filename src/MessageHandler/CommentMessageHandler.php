<?php

namespace App\MessageHandler;
use App\Message\Comment\Message;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
class CommentMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;

    public function __construct(EntityManagerInterface $entityManager, CommentRepository $commentRepository)
    {
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
    }
    public function __invoke(Message $message): void
    {
        sleep(10);
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment) {
            return;
        }

        $comment->setState('published');

        $this->entityManager->flush();
    }
}