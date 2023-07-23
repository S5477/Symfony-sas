<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiStubController extends AbstractController
{
    #[Route('/apistub', name: 'apistub')]
    public function api(Request $request, CommentRepository $commentRepository): void
    {
         $id = $request->query->get('id');
         $comment = $commentRepository->find($id);
         sleep(10);
        $comment;


    }
}
