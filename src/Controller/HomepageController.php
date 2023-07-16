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
        $all = $repository->findAll();

        // Keep only the first 15 tricks
        $firstFifteenTricks = array_slice($all, 0, 15);
//        dd($firstFifteenTricks);

        return $this->render('homepage/index.html.twig', [
            'tricks' => $firstFifteenTricks,
        ]);
    }
}
