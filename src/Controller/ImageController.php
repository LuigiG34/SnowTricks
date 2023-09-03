<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ImageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class ImageController extends AbstractController
{
    #[Route('/image/delete/{id}', name: 'app_delete_image', methods: ['DELETE'])]
    public function deleteImage($id, Request $request, ImageRepository $imageRepository): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $image = $imageRepository->find($id);

            if ($this->isCsrfTokenValid('delete' . $image->getId(), $request->headers->get('X-CSRF-TOKEN'))) {
    
                $nameImageBool = file_exists($this->getParameter('images_directory') . $image->getName());

                if ($nameImageBool !== false) {
                    unlink($this->getParameter('images_directory') . $image->getName());
                }
                
                $imageRepository->remove($image, true);
                return new JsonResponse(['message' => 'Image deleted successfully'], Response::HTTP_OK);
            }
        }
    }
}
