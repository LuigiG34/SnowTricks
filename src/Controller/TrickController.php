<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TrickRepository;

class TrickController extends AbstractController
{
    #[Route('/tricks/{slug}', name: 'app_trick')]
    public function index($slug, TrickRepository $repository): Response
    {
        $unique = $repository->findOneBy(['slug' => $slug]);

        return $this->render('trick/index.html.twig', [
            'trick' => $unique,
        ]);
    }
}
