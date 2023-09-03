<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\VideoRepository;

class VideoController extends AbstractController
{
    #[Route('/video/delete/{id}', name: 'app_delete_video')]
    public function deleteVideo($id, Request $request, VideoRepository $videoRepository): Response
    {
        $video = $videoRepository->find($id);

        if ($this->isCsrfTokenValid('delete' . $video->getId(), $request->request->get('_token'))) {

            $videoRepository->remove($video, true);
        }

        $this->addFlash('success', 'Video deleted successfully.');
        return $this->redirectToRoute('trick_show', ['slug' => $video->getTrick()->getSlug()]);
    }
}
