<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Request;

class HomepageController extends AbstractController
{
    #[Route('/{offset}', name: 'app_homepage', methods: ['GET'], defaults: ['offset' => 0], priority: -1)]
    public function index(TrickRepository $repository, Request $request, int $offset): Response
    {
        $paginator = $repository->getTrickPaginator(max(0, $offset));
        
        if($request->isXmlHttpRequest()) {
        
            $paginator = $repository->getTrickPaginator($offset);

            $html = $this->renderView('_partials/_tricks.html.twig', [
                'tricks' => $paginator,
            ]);

            return new Response($html);
        }

        return $this->render('homepage/index.html.twig', [
            'tricks' => $paginator,
        ]);
    }
}