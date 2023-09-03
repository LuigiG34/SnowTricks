<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\VideoRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class VideoController extends AbstractController
{
    #[Route('/video/delete/{id}', name: 'app_delete_video', methods: ['DELETE'])]
    public function deleteVideo($id, Request $request, VideoRepository $videoRepository): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $video = $videoRepository->find($id);

            if ($this->isCsrfTokenValid('delete' . $video->getId(), $request->headers->get('X-CSRF-TOKEN'))) {
                $videoRepository->remove($video, true);
            }

            return new JsonResponse(['message' => 'Video deleted successfully'], Response::HTTP_OK);
        }
    }
}
