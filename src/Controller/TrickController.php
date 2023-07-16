<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    #[Route('/get-more-tricks/{offset}/{limit}', name: 'get_more_tricks', methods: ['GET'])]
    public function getMoreTricks(TrickRepository $repository, $offset, $limit): JsonResponse
    {
        $all = $repository->findAll();



        $tricksData = [];
        foreach ($all as $trick) {
            $firstImage = null;
            $images = $trick->getImages();
            if (!empty($images)) {
                $firstImage = $images[0]->getPath();
            } else {
                $firstImage = "/assets/img/default.jpg";
            }

            $tricksData[] = [
                'name' => $trick->getName(),
                'slug' => $trick->getSlug(),
                'image' => $firstImage,
            ];
        }

        $moreTricks = array_slice($tricksData, $offset, $limit);

        return new JsonResponse($moreTricks);
    }
}
