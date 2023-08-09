<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TrickRepository;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(TrickRepository $repository): Response
    {
        $tricks = $repository->findBy([],['createdAt' => 'ASC'], 9);

        return $this->render('homepage/index.html.twig', [
            'tricks' => $tricks,
        ]);
    }
}
