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
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class TrickController extends AbstractController
{

    #[Route('/tricks/{slug}', name: 'app_trick')]
    public function index(Trick $trick, Request $request, EntityManagerInterface $manager): Response
    {
        $comment = new Comment;
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $comment->setContent($comment->getContent());
                $comment->setTrick($trick);
                $comment->setUser($this->getUser());
                $manager->persist($comment);
                $manager->flush();

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


    // à modifier
    #[Route('/get-more-tricks/{offset}/{limit}', name: 'get_more_tricks', methods: ['GET'])]
    public function getMoreTricks(TrickRepository $repository, $offset, $limit): JsonResponse
    {
        // MODIFIER : Créer un parser -> prendre des tricks et l'afficher en HTML
        // MODIFIER : Mettre offset et limit dans la requete SQL directement 
        // MODIFIER : Retourne du HTML au lieu de JSON

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





    #[Route('/tricks/edit/{slug}', name: 'app_edit_trick')]
    public function updateTrick(Trick $trick, TrickRepository $repository, Request $request): Response
    {
        // Ajouter validation sur les champs
        // Ajouter video et image directement dans le formulaire 

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                dd($trick, $form->getData());

                $repository->save($trick);
    
                $this->addFlash('success', 'Trick updated successfully !');
            }else{
                dd($trick, $form->getData(), $form->getErrors(true, true), $_POST, $_FILES);
                
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
