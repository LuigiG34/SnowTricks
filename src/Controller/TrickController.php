<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Service\TrickService;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/tricks', name: 'trick_')]
class TrickController extends AbstractController
{

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function addTrick(TrickRepository $repository, TrickService $trickService, Request $request): Response
    {
        $trick = new Trick;
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                // On traite l'image principale, les images secondaires et l'url de la vidéo YouTube
                $trickService->processMedia($trick, $this->getParameter('images_directory'), $form->get('mainImageFile')->getData(), $form->get('multiple_images')->getData(), $form->get('video_url')->getData());

                // On ajoute l'auteur
                $trick->setUser($this->getUser());
                // On enregistre le trick
                $repository->save($trick, true);

                $this->addFlash('success', 'Trick added successfully !');
                return $this->redirectToRoute('trick_show', [
                    'slug' => $trick->getSlug()
                ]);

            } else {

                $errors = $form->getErrors(true, true);

                foreach ($errors as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
                return $this->redirectToRoute('trick_add');
            }
        }

        return $this->render('trick/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{slug}/{offset}', name: 'show', methods: ['GET', 'POST'], defaults: ['offset' => 0])]
    public function index(Trick $trick, Request $request, CommentRepository $commentRepository, int $offset): Response
    {
        $comments = $commentRepository->getCommentPaginator($trick, max(0, $offset));

        if($request->isXmlHttpRequest()) {
        
            $paginator = $commentRepository->getCommentPaginator($trick, max(0, $offset));

            $html = $this->renderView('_partials/_comments.html.twig', [
                'comments' => $paginator
            ]);

            return new Response($html);
        }


        $comment = new Comment;
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $comment->setContent($comment->getContent());
                $comment->setTrick($trick);
                $comment->setUser($this->getUser());
                $commentRepository->save($comment, true);

                $this->addFlash('success', 'Comment added successfully !');
            } else {
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            }
            return $this->redirectToRoute('app_trick', ['slug' => $trick->getSlug()]);
        }

        return $this->render('trick/index.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'comments' => $comments
        ]);
    }

    #[Route('/edit/{slug}', name: 'edit', methods: ['GET', 'POST'])]
    public function updateTrick(Trick $trick, TrickRepository $repository, TrickService $trickService, ImageRepository $imageRepository, Request $request): Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            
            if ($form->isValid()) {
                
                // Remplacer l'image princiapl actuel si elle existe
                $trickService->updateMainImage($trick, $form->get('mainImageFile')->getData(), $this->getParameter('images_directory'), $imageRepository);
                // On traite l'image principale, les images secondaires et l'url de la vidéo YouTube
                $trickService->processMedia($trick, $this->getParameter('images_directory'), $form->get('mainImageFile')->getData(), $form->get('multiple_images')->getData(), $form->get('video_url')->getData());

                // On ajoute l'auteur
                $trick->setUser($this->getUser());
                // On enregistre le trick
                $repository->save($trick, true);

                $this->addFlash('success', 'Trick updated successfully !');
            } else {

                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            }

            return $this->redirectToRoute('trick_edit', [
                'slug' => $trick->getSlug()
            ]);
        }

        return $this->render('trick/update.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick
        ]);
    }

    #[Route('/delete/{slug}', name: 'delete', methods: ['DELETE'])]
    public function deleteTrick(Trick $trick, Request $request, TrickRepository $trickRepository): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            if ($this->isCsrfTokenValid('delete' . $trick->getSlug(), $request->headers->get('X-CSRF-TOKEN'))) {

                $images = $trick->getImages();
    
                foreach ($images as $image) {
                    $nameImageBool = file_exists($this->getParameter('images_directory') . $image->getName());
    
                    if ($nameImageBool !== false) {
                        unlink($this->getParameter('images_directory') . $image->getName());
                    }
                }
    
                $trickRepository->remove($trick, true);
                return new JsonResponse(['message' => 'Trick deleted successfully'], Response::HTTP_OK);
            }
        }
    }
}
