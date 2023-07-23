<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Message\Comment\Message;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ConferenceController extends AbstractController
{
    private Environment $twig;
    private EntityManagerInterface $entityManager;

    private MessageBusInterface $bus;

    public function __construct(
        Environment $twig,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    )
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return new Response($this->twig->render(
            'conference/index.html.twig',
            )
        );
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route('/conference/{slug}', name: 'conference')]
    public function show(
        Request $request,
        Conference $conference,
        CommentRepository $commentRepository,
        string $photoDir
    ): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);

            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)). '.' . $photo->guessExtension();

                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    throw new \Exception($e->getMessage());
                }

                $comment->setPhotoFileName($filename);
            }

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $this->bus->dispatch(new Message($comment->getId()));

            return $this->redirectToRoute('conference', [
                'slug' => $conference->getSlug(),
            ]);
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);

        return new Response($this->twig->render(
                'conference/show.html.twig',
                [
                    'conference' => $conference,
                    'comments' => $paginator,
                    'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
                    'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
                    'comment_form' => $form->createView(),
                ]
            )
        );

    }
}
