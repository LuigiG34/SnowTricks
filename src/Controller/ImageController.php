<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ImageRepository;

class ImageController extends AbstractController
{
    #[Route('/image/delete/{id}', name: 'app_delete_image')]
    public function deleteImage($id, Request $request, ImageRepository $imageRepository): Response
    {
        $image = $imageRepository->find($id);

        if ($this->isCsrfTokenValid('delete' . $image->getId(), $request->request->get('_token'))) {

            $nameImageBool = file_exists($this->getParameter('images_directory') . $image->getName());

            if ($nameImageBool !== false) {
                unlink($this->getParameter('images_directory') . $image->getName());
            }

            $imageRepository->remove($image, true);
        }

        $this->addFlash('success', 'Image deleted successfully.');
        return $this->redirectToRoute('trick_show', ['slug' => $image->getTrick()->getSlug()]);
    }
}
