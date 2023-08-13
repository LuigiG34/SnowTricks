<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Repository\VideoRepository;


class TrickController extends AbstractController
{

    #[Route('/tricks/{slug}', name: 'app_trick')]
    public function index(Trick $trick, Request $request, CommentRepository $commentRepository): Response
    {
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
        ]);
    }

    // Ajouter les commentaires en AJAX

    #[Route('/add/trick', name: 'app_add_trick')]
    public function addTrick(TrickRepository $repository, Request $request): Response
    {
        // Ajouter validation sur les champs
        // Ajouter video et image directement dans le formulaire 

        $trick = new Trick;
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                $repository->save($trick);
    
                $this->addFlash('success', 'Trick added successfully !');
            }else{

                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            }
            
            return $this->redirectToRoute('app_edit_trick', [
                'slug' => $trick->getSlug()
            ]);
        }

        return $this->render('trick/add.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/tricks/edit/{slug}', name: 'app_edit_trick')]
    public function updateTrick(Trick $trick, TrickRepository $repository, Request $request): Response
    {
        // Ajouter validation sur les champs
        // Ajouter video et image directement dans le formulaire 

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                $repository->save($trick);
    
                $this->addFlash('success', 'Trick updated successfully !');
            }else{
                
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            }
            
            return $this->redirectToRoute('app_edit_trick', [
                'slug' => $trick->getSlug()
            ]);
        }

        return $this->render('trick/update.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick
        ]);
    }

    #[Route('/tricks/delete/{slug}', name: 'app_delete_trick')]
    public function deleteTrick(Trick $trick, Request $request, TrickRepository $trickRepository): Response
    {

        if ($this->isCsrfTokenValid('delete' . $trick->getSlug(), $request->request->get('_token'))) {

            $images = $trick->getImages();

            foreach ($images as $image) {
                $nameImageBool = file_exists($this->getParameter('public_directory') . $image->getPath());

                if ($nameImageBool !== false) {
                    unlink($this->getParameter('public_directory') . $image->getPath());
                }
            }

            $trickRepository->remove($trick, true);
        }

        $this->addFlash('success', 'Trick deleted successfully.');
        return $this->redirectToRoute('app_homepage');
    }

    #[Route('/image/delete/{id}', name: 'app_delete_image')]
    public function deleteImage($id, Request $request, ImageRepository $imageRepository): Response
    {
        $image = $imageRepository->find($id);

        if ($this->isCsrfTokenValid('delete' . $image->getId(), $request->request->get('_token'))) {

            $nameImageBool = file_exists($this->getParameter('public_directory') . $image->getPath());

            if ($nameImageBool !== false) {
                unlink($this->getParameter('public_directory') . $image->getPath());
            }

            $imageRepository->remove($image, true);
        }

        $this->addFlash('success', 'Image deleted successfully.');
        return $this->redirectToRoute('app_trick', ['slug' => $image->getTrick()->getSlug()]);
    }

    #[Route('/video/delete/{id}', name: 'app_delete_video')]
    public function deleteVideo($id, Request $request, VideoRepository $videoRepository): Response
    {
        $video = $videoRepository->find($id);

        if ($this->isCsrfTokenValid('delete' . $video->getId(), $request->request->get('_token'))) {

            $videoRepository->remove($video, true);
        
        }

        $this->addFlash('success', 'Video deleted successfully.');
        return $this->redirectToRoute('app_trick', ['slug' => $video->getTrick()->getSlug()]);
    }
}
